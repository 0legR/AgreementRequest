<?php
namespace App\Services\Accountancy;

use App\Models\accountancy\agreements\AgreementRequest;
use App\Helpers\Accountancy\ServiceHelper;
use App\Builders\Traits\AccountancyTrait;
use App\Models\user\Accountant;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewAgreementRequest;
use App\Services\ExceptionsService;
use App\Services\StudentRoleService;

/**
 * serve AgreementRequest model
 */
class AgreementRequestService
{
	use AccountancyTrait;

	protected $service;
	protected $contentService;
	protected $contentType;

	/**
	 * gets Service $service and CourseService $courseService or ModuleService $moduleService
	 */
	public function getService($ARData)
	{
		$serviceHelper = new ServiceHelper($ARData['service']);
        $this->service = $serviceHelper->serviceByContentAndEduStr($ARData['content_id'], $ARData['educ_form']);
        $this->contentService = $serviceHelper->contentService;
        $this->contentType = $ARData['service'];
	}
	/**
	 * creates new agreement reuest
	 * @param $ARData array with form data
	 * @return array with response data and AgreementRequest $agreementRequest
	 */
	public function newAgreementRequest($ARData)
	{
		$aRequest;
		try{
            \DB::transaction(function () use ($ARData, &$aRequest) {
            	$this->getService($ARData);
		        $user = \Auth::user();
		        $agreementRequest = new AgreementRequest();
		        $agreementRequest->fill([
		            'user_id' => $user->id,
		            'service_id' => $this->service->service_id,
		            'scheme_id' => $ARData['scheme_id']
		        ]);
		        $errors = $agreementRequest->validate();
		        if (empty($errors)) {
		            $agreementRequest->save();
		            $aRequest = $agreementRequest;
		            $studentService = new StudentRoleService($agreementRequest->user, $this->getOrganization());
		        	$studentService->createIfNotExists();
		            $this->notifyAccountants($agreementRequest->createMessage(), $agreementRequest->createSubject(), $agreementRequest);
		        } else {
		            $exceptionService = new ExceptionsService(strval(__('messages.915')));
        			$exceptionService->JSONString();
		        }
            });
        } catch(\Exception $e) {
        	$exceptionService = new ExceptionsService(strval($e->getMessage()));
        	$exceptionService->JSONString();
        }
        return $this->createResponse(null, ['agreementRequest' => $aRequest]);
	}

	/**
	 * sends mail and makes notifications to active accountants of service organization
	 * @param $message string body of message
	 * @param $subject string header of message
	 * @param AgreementRequest $agreementRequest
	 * 
	 */
	public function notifyAccountants($message, $subject, $agreementRequest)
	{
		$accountants = Accountant::onlyEmptyEndDate()->byJobOrganization($this->getOrganization())->get();
		$messageData = [
			'message_text' => $message,
			'subject' => $subject,
		];
		$accountants->each(function ($accountant) use ($messageData, $agreementRequest) {
			$messageData['receiver'] = $accountant->id_user;
			$accountant->notify($messageData);
			Mail::to($accountant->user)
                ->send(new NewAgreementRequest($messageData, $agreementRequest));
		});
	}

	/**
	 * get organization id from course or module
	 * @return int $organization_id;
	 */
	public function getOrganization()
	{
		$relationName = "{$this->contentType}Model";
		return $this->contentService->$relationName->id_organization;
	}
	
}