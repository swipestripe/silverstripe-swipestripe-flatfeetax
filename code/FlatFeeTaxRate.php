<?php
/**
 * Tax rates that can be set in {@link SiteConfig}. Several flat rates can be set 
 * for any supported shipping country.
 */
class FlatFeeTaxRate extends DataObject {
	
	/**
	 * Fields for this tax rate
	 * 
	 * @var Array
	 */
	private static $db = array(
		'Title' => 'Varchar',
		'Description' => 'Varchar',
		'Rate' => 'Decimal(18,2)'
	);
	
	/**
	 * Tax rates are associated with SiteConfigs.
	 * 
	 * @var unknown_type
	 */
	private static $has_one = array(
		'ShopConfig' => 'ShopConfig',
		'Country' => 'Country_Shipping'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'Description' => 'Description',
		'SummaryOfRate' => 'Rate',
		'Country.Title' => 'Country'
	);
	
	/**
	 * Field for editing a {@link FlatFeeTaxRate}.
	 * 
	 * @return FieldSet
	 */
	public function getCMSFields() {

		return new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('TaxRate',
					TextField::create('Title', _t('FlatFeeTaxRate.TITLE', 'Title')),
					TextField::create('Description', _t('FlatFeeTaxRate.DESCRIPTION', 'Description')),
					DropdownField::create('CountryID', _t('FlatFeeTaxRate.COUNTRY', 'Country'), Country_Shipping::get()->map()->toArray()),
					NumericField::create('Rate', _t('FlatFeeTaxRate.TAX_RATE', 'Tax rate'))
						->setRightTitle('As a percentage (%)')
				)
			)
		);
	}
	
	/**
	 * Label for using on {@link FlatFeeTaxModifierField}s.
	 * 
	 * @see FlatFeeTaxModifierField
	 * @return String
	 */
	public function Label() {
		return $this->Title . ' ' . $this->SummaryOfRate();
	}
	
	/**
	 * Summary of the current tax rate
	 * 
	 * @return String
	 */
	public function SummaryOfRate() {
		return $this->Rate . ' %';
	}

	public function getAmount($order) {
		$amount = new Price();
		$amount->setCurrency(ShopConfig::current_shop_config()->BaseCurrency);
		$amount->setSymbol(ShopConfig::current_shop_config()->BaseCurrencySymbol);
		$amount->setAmount($order->SubTotal()->getAmount() * ($this->Rate / 100));
		return $amount;
	}

	public function Amount($order) {

		$shopConfig = ShopConfig::current_shop_config();

		$amount = new Price();
		$amount->setAmount($order->SubTotal()->getAmount() * ($this->Rate / 100));
		$amount->setCurrency($shopConfig->BaseCurrency);
		$amount->setSymbol($shopConfig->BaseCurrencySymbol);
		return $amount;
	}

	/**
	 * Display price, can decorate for multiple currency etc.
	 * 
	 * @return Price
	 */
	public function Price($order) {
		
		$amount = $this->Amount($order);
		$this->extend('updatePrice', $amount);
		return $amount;
	}
}

/**
 * So that {@link FlatFeeTaxRate}s can be created in {@link SiteConfig}.
 */
class FlatFeeTaxRate_Extension extends DataExtension {

	/**
	 * Attach {@link FlatFeeTaxRate}s to {@link SiteConfig}.
	 * 
	 * @see DataObjectDecorator::extraStatics()
	 */
	public static $has_many = array(
		'FlatFeeTaxRates' => 'FlatFeeTaxRate'
	);

}

class FlatFeeTaxRate_Admin extends ShopAdmin {

	private static $tree_class = 'ShopConfig';
	
	private static $allowed_actions = array(
		'TaxSettings',
		'TaxSettingsForm',
		'saveTaxSettings'
	);

	private static $url_rule = 'ShopConfig/Tax';
	protected static $url_priority = 120;
	private static $menu_title = 'Shop Tax Rates';

	private static $url_handlers = array(
		'ShopConfig/Tax/TaxSettingsForm' => 'TaxSettingsForm',
		'ShopConfig/Tax' => 'TaxSettings'
	);

	public function init() {
		parent::init();
		$this->modelClass = 'ShopConfig';
	}

	public function Breadcrumbs($unlinked = false) {

		$request = $this->getRequest();
		$items = parent::Breadcrumbs($unlinked);

		if ($items->count() > 1) $items->remove($items->pop());

		$items->push(new ArrayData(array(
			'Title' => 'Tax Settings',
			'Link' => $this->Link(Controller::join_links($this->sanitiseClassName($this->modelClass), 'Tax'))
		)));

		return $items;
	}

	public function SettingsForm($request = null) {
		return $this->TaxSettingsForm();
	}

	public function TaxSettings($request) {

		if ($request->isAjax()) {
			$controller = $this;
			$responseNegotiator = new PjaxResponseNegotiator(
				array(
					'CurrentForm' => function() use(&$controller) {
						return $controller->TaxSettingsForm()->forTemplate();
					},
					'Content' => function() use(&$controller) {
						return $controller->renderWith('ShopAdminSettings_Content');
					},
					'Breadcrumbs' => function() use (&$controller) {
						return $controller->renderWith('CMSBreadcrumbs');
					},
					'default' => function() use(&$controller) {
						return $controller->renderWith($controller->getViewer('show'));
					}
				),
				$this->response
			); 
			return $responseNegotiator->respond($this->getRequest());
		}

		return $this->renderWith('ShopAdminSettings');
	}

	public function TaxSettingsForm() {

		$shopConfig = ShopConfig::get()->First();

		$fields = new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('Tax',
					GridField::create(
						'FlatFeeTaxRates',
						'FlatFeeTaxRates',
						$shopConfig->FlatFeeTaxRates(),
						GridFieldConfig_HasManyRelationEditor::create()
					)
				)
			)
		);

		$actions = new FieldList();
		$actions->push(FormAction::create('saveTaxSettings', _t('GridFieldDetailForm.Save', 'Save'))
			->setUseButtonTag(true)
			->addExtraClass('ss-ui-action-constructive')
			->setAttribute('data-icon', 'add'));

		$form = new Form(
			$this,
			'EditForm',
			$fields,
			$actions
		);

		$form->setTemplate('ShopAdminSettings_EditForm');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm');
		$form->addExtraClass('cms-content cms-edit-form center ss-tabset');
		if($form->Fields()->hasTabset()) $form->Fields()->findOrMakeTab('Root')->setTemplate('CMSTabSet');
		$form->setFormAction(Controller::join_links($this->Link($this->sanitiseClassName($this->modelClass)), 'Tax/TaxSettingsForm'));

		$form->loadDataFrom($shopConfig);

		return $form;
	}

	public function saveTaxSettings($data, $form) {

		//Hack for LeftAndMain::getRecord()
		self::$tree_class = 'ShopConfig';

		$config = ShopConfig::get()->First();
		$form->saveInto($config);
		$config->write();
		$form->sessionMessage('Saved Tax Settings', 'good');

		$controller = $this;
		$responseNegotiator = new PjaxResponseNegotiator(
			array(
				'CurrentForm' => function() use(&$controller) {
					//return $controller->renderWith('ShopAdminSettings_Content');
					return $controller->TaxSettingsForm()->forTemplate();
				},
				'Content' => function() use(&$controller) {
					//return $controller->renderWith($controller->getTemplatesWithSuffix('_Content'));
				},
				'Breadcrumbs' => function() use (&$controller) {
					return $controller->renderWith('CMSBreadcrumbs');
				},
				'default' => function() use(&$controller) {
					return $controller->renderWith($controller->getViewer('show'));
				}
			),
			$this->response
		); 
		return $responseNegotiator->respond($this->getRequest());
	}

	public function getSnippet() {

		if (!$member = Member::currentUser()) return false;
		if (!Permission::check('CMS_ACCESS_' . get_class($this), 'any', $member)) return false;

		return $this->customise(array(
			'Title' => 'Tax Management',
			'Help' => 'Create tax rates',
			'Link' => Controller::join_links($this->Link('ShopConfig'), 'Tax'),
			'LinkTitle' => 'Edit tax rates'
		))->renderWith('ShopAdmin_Snippet');
	}

}