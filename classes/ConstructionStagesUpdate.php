<?php

class ConstructionStagesUpdate
{
    public $validationService;

	public $name;
	public $startDate;
	public $endDate;
	public $duration;
	public $durationUnit;
	public $color;
	public $externalId;
	public $status;

	public function __construct($data) {

        $this->validationService = new Validation();

		if(is_object($data)) {

			$vars = get_object_vars($this);
            $errors = [];
			foreach ($vars as $name => $value) {

				if (isset($data->$name)) {
					$this->$name = $data->$name;
				}
			}

            if (count($errors) > 0) {
                
                return $errors;
            }
		}
        
	}

    public function validate(Array $forms)
    {
        $errors = [];
        foreach ($forms as $name => $form) {

            // parse validation rules
            $validations = explode('|',$form);

            // validate input form
            foreach($validations as $validation) {

                // parse validation variables
                $validation = explode(':',$validation);
                $rule = $validation[0];
                $ruleVars = isset($validation[1]) ? $validation[1] : null;
                
                if ($rule == "after") {
                    $validated = $this->validationService->$rule($this->$name, $name, $ruleVars, $ruleVars ? $this->$ruleVars : null);
                } else {
                    $validated = $this->validationService->$rule($this->$name, $name, $ruleVars);
                }

                if($validated !== true) {
                    array_push($errors, [$name => $validated]);
                }
            }
        }

        if(count($errors) > 0) {
            return ['errors' => $errors];
        }
    }

}