<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


////////////////////////Role routes////////////////////////
$route['roles']                       = 'RoleController/roles';  //Get -> show roles  & post -> create role
$route['roles/update/(:num)']         = 'RoleController/updateRole/id/$1';

////////////////////////Permission routes////////////////////////
$route['permissions']                 = 'PermissionController/permissions'; //Get -> show permissions  & post -> create permission

////////////////////////User routes////////////////////////
$route['users']                       = 'UserController/users'; //Get -> show users  & post -> create users
$route['users/(:num)']                = 'UserController/getUser/id/$1';
$route['users/update/(:num)']         = 'UserController/updateUser/id/$1';
$route['users/remove/(:num)']         = 'UserController/removeUser/id/$1';
$route['users/roles/(:num)']          = 'UserController/updateRoles/id/$1';

//Recover or change password
$route['recoverPassword']             = 'UserController/recoverPassword';
$route['changePassword']              = 'UserController/changePassword';

////////////////////////Bank routes////////////////////////
$route['banks']                       = 'BankController/banks';  //Get -> show banks  & post -> create bank
$route['banks/update/(:num)']         = 'BankController/updateBank/id/$1';
$route['banks/remove/(:num)']         = 'BankController/removeBank/id/$1';
$route['banks/(:num)']                = 'BankController/getBank/id/$1';

////////////////////////Speciality routes////////////////////////
$route['specialities']                = 'SpecialityController/specialities';  //Get -> show specialities  & post -> create speciality
$route['specialities/update/(:num)']  = 'SpecialityController/updateSpeciality/id/$1';
$route['specialities/remove/(:num)']  = 'SpecialityController/removeSpeciality/id/$1';
$route['specialities/(:num)']         = 'SpecialityController/getSpeciality/id/$1';


////////////////////////Medical Insurances routes////////////////////////
$route['insurances']                  = 'MedicalInsuranceController/medicalInsurance';  //Get -> show banks  & post -> create bank
$route['insurances/update/(:num)']    = 'MedicalInsuranceController/updateInsurance/id/$1';
$route['insurances/remove/(:num)']    = 'MedicalInsuranceController/removeInsurance/id/$1';
$route['insurances/(:num)']           = 'MedicalInsuranceController/getInsurance/id/$1';

////////////////////////Plans routes////////////////////////
$route['plans']                       = 'PlanController/plans';  //post -> create plan
$route['plansByInsurance/(:num)']     = 'PlanController/plansByInsurance/id/$1';  //get -> get plans by insurance
$route['plans/update/(:num)']         = 'PlanController/updatePlan/id/$1';
$route['plans/remove/(:num)']         = 'PlanController/removePlan/id/$1';
$route['plans/(:num)']                = 'PlanController/getPlan/id/$1';
$route['plans/fee/(:num)']            = 'PlanController/getPlanByFeeId/id/$1';

////////////////////////Contact routes////////////////////////
$route['contacts']                    = 'ContactController/contacts';  //Get -> show contacts  & post -> create contact
$route['contacts/update/(:num)']      = 'ContactController/updateContact/id/$1';
$route['contacts/remove/(:num)']      = 'ContactController/removeContact/id/$1';
$route['contacts/(:num)']             = 'ContactController/getContact/id/$1';

////////////////////////Nomenclator routes////////////////////////
$route['nomenclators']                = 'NomenclatorController/nomenclators';  //Get -> show contacts  & post -> create contact
$route['nomenclators/update/(:num)']  = 'NomenclatorController/updateNomenclator/id/$1';
$route['nomenclators/remove/(:num)']  = 'NomenclatorController/removeNomenclator/id/$1';
$route['nomenclators/(:num)']         = 'NomenclatorController/getNomenclator/id/$1';

////////////////////////Credit-debit-concepts routes////////////////////////
$route['cdconcepts']                  = 'CreditDebitConceptController/concepts'; //Get -> show concepts  & post -> create concept
$route['cdconcepts/(:num)']           = 'CreditDebitConceptController/concepts/id/$1'; //Get -> show specific concept, Put -> update concept , Delete -> delete concept

////////////////////////Fee routes////////////////////////
$route['fees']                        = 'FeeController/fees';  //Get -> show fees  & post -> create fee & put -> update fees
$route['fees/(:num)']                 = 'FeeController/fees/id/$1';
$route['fees/increment']              = 'FeeController/incrementedFees';   //Post: create new fees with new values

////////////////////////Benefit routes////////////////////////
$route['benefits']                    = 'BenefitController/benefits';  //Get -> show benefits  & post -> create benefit & put -> update benefit
$route['benefits/(:num)']             = 'BenefitController/benefits/id/$1';

////////////////////////IVA routes////////////////////////
$route['iva']                         = 'IvaController/iva';  //Get -> show iva

////////////////////////Scope routes////////////////////////
$route['scopes']                      = 'ScopeController/scopes';  //Get -> show scopes

////////////////////////Login routes////////////////////////
$route['login']['post']               = 'LoginController/login';

////////////////////////IVA routes////////////////////////
$route['iva']                         = 'IvaController/iva';  //Get -> show iva

////////////////////////Patient routes////////////////////////
$route['affiliates']                  = 'AffiliateController/affiliate';  //Get -> show affiliates

////////////////////////Femeba routes////////////////////////
$route['categoryfemeba']              = 'CategoryFemebaController/femeba';

////////////////////////Category Circle routes////////////////////////
$route['medicalcareer']               = 'MedicalCareerController/medical_career';

////////////////////////Billing codes routes////////////////////////
$route['billingCodes']                = 'BillingCodeController/billing_code';

////////////////////////Holiday options routes////////////////////////
$route['holidayOptions']              = 'HolidayOptionController/holiday_option';

////////////////////////Maternal plan options routes////////////////////////
$route['maternalPlanOptions']         = 'MaternalPlanOptionController/maternal_plan_option';

////////////////////////Internment-ambulatory options routes////////////////////////
$route['internmentAmbulatoryOptions'] = 'InternmentAmbulatoryOptionController/internment_ambulatory_option';

////////////////////////Concept Group routes////////////////////////
$route['conceptgroup']                = 'ConceptGroupController/conceptGroups';

////////////////////////Payment Types routes////////////////////////
$route['paymenttypes']                = 'PaymentTypesController/payment_types';

////////////////////////Professionals routes////////////////////////
$route['professionals']               = 'ProfessionalsController/professionals'; //Get -> show professionals  & post -> create professionals
$route['professionals/(:num)']        = 'ProfessionalsController/getProfessionals/id/$1';
$route['professionals/update/(:num)'] = 'ProfessionalsController/updateProfessionals/id/$1';
$route['professionals/remove/(:num)'] = 'ProfessionalsController/removeProfessional/id/$1';

////////////////////////Coverage routes////////////////////////
$route['coverages']                   = 'CoverageController/coverages';
$route['coverages/(:num)']            = 'CoverageController/getCoverage/id/$1';
$route['coverages/update/(:num)']     = 'CoverageController/updateCoverage/id/$1';
$route['coverages/remove/(:num)']     = 'CoverageController/removeCoverage/id/$1';

////////////////////////Special Conditions routes////////////////////////
$route['specialconditionstypes']            = 'SpecialConditionsTypesController/types';
$route['specialconditions']                 = 'SpecialConditionsController/specialconditions';
$route['specialconditions']                 = 'SpecialConditionsController/specialconditions';
$route['specialconditions/update/(:num)']   = 'SpecialConditionsController/specialconditions/id/$1';
$route['specialconditions/remove/(:num)']   = 'SpecialConditionsController/specialconditions/id/$1';
$route['specialconditions/(:num)']          = 'SpecialConditionsController/specialconditions_by_id/id/$1';

//////////////////////// Manage Earnings  ////////////////////////

$route['earnings']                    = 'EarningsController/earnings';
$route['earnings/(:num)']             = 'EarningsController/earnings/id/$1';

////////////////////////Autocomplete services routes////////////////////////
$route['insurances/like']             = 'MedicalInsuranceController/insuranceData';
$route['nomenclators/like']           = 'NomenclatorController/nomenclatorData';
$route['professionals/like']          = 'ProfessionalsController/professionalsData';

//Test validator
$route['benefits/test']             = 'BenefitController/validar';


////////////////////////Billing Process////////////////////////
$route['bill']      = 'BillController/bill';




