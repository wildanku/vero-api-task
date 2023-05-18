<?php 

class Validation 
{
    /**
     * a function to validate if field is required
     *
     * @param mixed $value
     * @param mixed $name
     * @param mixed $rules
     * @return void
     */
    public function required(mixed $value, $name, $rules = null)
    {
        $message = null;

        // validate if field is empty
        if(!isset($value) || strlen($value) == 0){
            $message = $name.' field cannot be empty!';
            return $message;
        }

        return true;
    }

    /**
     * a function to validate the maximum of field length
     *
     * @param mixed $value
     * @param mixed $name
     * @param mixed $rules
     * @return void
     */
    public function max(mixed $value, mixed $name, mixed $rules = null)
    {   
        // validate if field is empty
        if(strlen($value) > $rules){
            return $name.' length cannot more than '.$rules;
        }

        return true;
    }

    /**
     * a function to validate input field must in defined variables
     *
     * @param mixed $value
     * @param mixed $name
     * @param mixed $rules
     * @return void
     */
    public function in(mixed $value, mixed $name, mixed $rules = null)
    {
        $mustIn = explode(',',$rules);

        if(isset($value) || strlen($value) == 0) {
            return true;
        }

        if(!in_array($value, $mustIn)) {
            return $name.' must in '.$rules;
        }

        return true;
    }

    /**
     * a function to validate dateTimeTz format
     *
     * @param mixed $value
     * @param mixed $name
     * @param mixed $rules
     * @return void
     */
    public function dateTimeTz(mixed $value, mixed $name, mixed $rules = null)
    {   
        if (!isset($value) || strlen($value) == 0) {
            return true;
        }

        if (preg_match('/^'.
            '(\d{4})-(\d{2})-(\d{2})T'. // YYYY-MM-DDT ex: 2014-01-01T
            '(\d{2}):(\d{2}):(\d{2})'.  // HH-MM-SS  ex: 17:00:00
            '(Z|((-|\+)\d{2}:\d{2}))'.  // Z or +01:00 or -01:00
            '$/', $value, $parts) == true)
        {
            try {
                new DateTime($value);
                return true;
            } catch ( \Exception $e) {
                return $name.' is not a valid date format, the date format must in iso8601 format i.e. 2022-12-31T14:59:00Z';;
            }
        } else {
            return $name.' is not a valid date format, the date format must in iso8601 format i.e. 2022-12-31T14:59:00Z';
        }
    }

    /**
     * a function to validate date after
     *
     * @param mixed $value
     * @param mixed $name
     * @param mixed $rules
     * @param mixed $rulesVar
     * @return void
     */
    public function after(mixed $value, mixed $name, mixed $rules = null, mixed $rulesVar = null)
    {
        if (!isset($value) || strlen($value) == 0) {
            return true;
        }

        if (!isset($rulesVar)) {
            return true;
        }

        try {
            $startAt = new DateTime($rulesVar);
        } catch (Exception $e) {
            return $rules.' is not a valid date format, the date format must in iso8601 format i.e. 2022-12-31T14:59:00Z';
        }

        try {
            $endAt = new DateTime($value);
        } catch (Exception $e) {
            return $name.' is not a valid date format, the date format must in iso8601 format i.e. 2022-12-31T14:59:00Z';
        }
        
        if ($startAt > $endAt) {
            return $name.' must be grather than '.$rules;
        }

        return true;
    }

    /**
     * a function to validate color format in HEX
     *
     * @param mixed $value
     * @param mixed $name
     * @param mixed $rules
     * @return void
     */
    public function hex(mixed $value, mixed $name, mixed $rules = null)
    {
        if(!isset($value) || strlen($value) == 0) {
            return true;
        }

        if (preg_match_all("/^#(?>[[:xdigit:]]{3}){1,2}$/", $value, $matches)){
            return true;
        }
        
        return $name.' is not a valid HEX format';
    }
}