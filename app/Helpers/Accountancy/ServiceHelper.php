<?php

namespace App\Helpers\Accountancy;

use App\Models\EducationForm;
use App\Services\ExceptionsService;

/**
 * serve service instance from acc_service table
 */
class ServiceHelper
{
	protected $contentType;
	protected $contentServiceModel;
	protected $upperContentType;
	public $contentService;
	protected $errors = [
		'serviceInDev' => 'State of service: "Developing"'
	];

	function __construct($contentType)
	{
		$this->contentType = $contentType;
		$this->upperContentType = ucfirst($contentType);
		$this->contentServiceFactory();
	}

	/**
	 * generate model path
	 */
	private function contentServiceFactory()
	{
		$this->contentServiceModel = "App\\Models\\accountancy\\services\\{$this->upperContentType}Service";
	}

	/**
	 * service by course_id || module_id and educaction form string
	 * @param $contentId int course_id || module_id
	 * @param $educForm string 'offline' || 'online'
	 * @return Service service
	 */
	public function serviceByContentAndEduStr($contentId, $educForm)
	{
		$educFormId = $educForm === 'offline' ? EducationForm::OFFLINE : EducationForm::ONLINE;
		$scopeName = "by{$this->upperContentType}AndEFId";
		$this->contentService = $this->contentServiceModel::$scopeName($contentId, $educFormId)->first();
	 	if(!$this->checkServiceReady($this->contentService)) {
	 		$exceptionsService = new ExceptionsService($this->errors['serviceInDev']);
	        $exceptionsService->JSONString();
        }
		return $this->contentService->service;
	}

	/**
	 * check is module or course ready for online and offline education form
	 * @param $contentService CourseService or ModuleService object
	 * @return boolean
	 */
	public function checkServiceReady($contentService) {
		$relationName = "{$this->contentType}Model";
        if($contentService->education_form === EducationForm::ONLINE && $contentService->$relationName->status_online) {
            return true;
        }
        if($contentService->education_form === EducationForm::OFFLINE && $contentService->$relationName->status_offline) {
            return true;
        }
        return false;
    }
}