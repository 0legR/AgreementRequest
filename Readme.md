#Example how to serve agreement request using Laravel framework
_Let's assume some authorized user wishes to pay for some Course on our project._
_User cames to page with Course, chose payment form and pushed the button 'Pay for Course'._
__The next events happen:__
1. The request comes to route "./routes/api.php"
	with url https://localhost.com/cabinet/agreement/requests (post method)
	with request params $agreementRequest = [
		'service' => $serviceId,
		'content_id' => $contentId,
		'educ_form' => $educFormId,
		'scheme_id' => $schemeId
	];
1. This route calls method 'store' in 'App\Http\Controllers\Cabinet\accountancy\AgreementRequestController';
1. Method store creates new instance of 'App\Services\Accountancy\AgreementRequestService' and calls method 'newAgreementRequest'
	with request params;
1. Method 'newAgreementRequest' opens Database transaction with request params and pointer to var
	(one gets newly created AgreementRequest as a result from success transaction).
	There are next actions inside transaction :
	1. method 'getService' calls ServiceHelper which checks is current content not in developing state
		and gets service data (service can be course or module instances)
	1. gets $user from Auth facade
	1. gets new AgreementRequest model instance
	1. fills all needed data to model instance
	1. checks due to validate method filled data
	1. if errors array is not empty call ExceptionsService and sends exception respone
	1. otherwise store AgreementRequest data to database and assign result to pointer var in transaction param
	1. due to StudentRoleService, checks is user has student role and if he doesn't assign student role to current user
	1. method notifyAccountants store to database notifiction and sends emails with message about newly created agreement request
		to every active users with accountant role
1. Method 'newAgreementRequest' return AgreementRequest object to method 'store' in 'App\Http\Controllers\Cabinet\accountancy\AgreementRequestController' and sends response to browser.

That's it! Appreciated for attention.
	
