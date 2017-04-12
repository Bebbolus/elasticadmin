<?php namespace App\Helper;


class FormInputCreator
{
	private $inputElementPerRow = 3;

	public function buildForm($fields)
    {
		$formBody = '';
        $i=0;
		foreach ($fields as $item) {

			$formBody .= $this->generateInput($item[0],$item[1],$item[2],$item[3],$item[4], $item[5]);

            $i++;
		}

		return $formBody;
	}

    /*
     * prende in input il tipo di field da costruire
     * ne restituisce la stringa html relativa
     */
    private function generateInput($type, $title, $name, $value, $option, $extra){
	    $md = 12/ $this->inputElementPerRow;
        if($type == 'text'){
            $formInputString = '
				<div class="col-md-'.$md.' col-xs-12">
	                <div class="form-control-wrapper">
	                    <div class="form-label">
	                        '.$title.'
	                    </div>
	                    <input type="text" id="inp_'.$name.'" class="form-control input-sm" name="'.$name.'" '.$extra.' value="'.$value.'">
	                </div>
                </div>
            ';
        }elseif($type == 'date'){
            if((string)(int)$value == $value){
                $value = date('d/m/Y', $value);
            }
            $formInputString = '
				<div class="col-md-'.$md.' col-xs-12">
	                <div class="form-control-wrapper">
	                    <div class="form-label">
	                         '.$title.'
	                    </div>
	                    <div class="input-group date" id="'.$name.'">
	                        <input class="form-control empty" name="'.$name.'" type="datetime-local" id="inp_'.$name.'" '.$extra.' value="'.$value.'">
	                    </div>
	                    
	                </div>
                </div>
            ';
        }elseif($type == 'select'){
            $formInputString = '
				<div class="col-md-'.$md.' col-xs-12">
	                <div class="form-control-wrapper">
	                    <div class="form-label">
	                        '.$title.'
	                    </div>
	                    <select class="form-control input-sm" name="'.$name.'" id="inp_'.$name.'" '.$extra.'>
	                        <option></option>';

            foreach ($option as $item) {
                $formInputString =  $formInputString .'<option ' ;
                $formInputString =  $formInputString .' value="'.$item.'"> '.$item.'</option>';
            }

             $formInputString =  $formInputString .'
                        </select>
                    </div>
                </div>
            ';
        }

        return $formInputString;
    }



}