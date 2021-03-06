<?
class EseraTemperatur extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->CreateVariableProfile("ESERA.Temperatur", 2, " ┬░C", -30, 150, 0, 2, "Temperature");

        $this->RegisterPropertyInteger("OWDID", 1);
        //$this->RegisterPropertyInteger("OWDFORMAT", 1);

        $this->RegisterVariableFloat("Temperatur", "Temperatur", "ESERA.Temperatur", 1);

        $this->ConnectParent("{FCABCDA7-3A57-657D-95FD-9324738A77B9}"); //1Wire Controller
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();

        //Apply filter
        $this->SetReceiveDataFilter(".*\"DeviceNumber\":". $this->ReadPropertyInteger("OWDID") .",.*");

    }
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("ESERA-Temperatur", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 0) {
                //Format der Temperaturen im 1-Wire Ethernetcontroller auslesen
                $ParentID = (IPS_GetInstance($this->InstanceID)['ConnectionID']);  
                $FORMAT=GetValueInteger(IPS_GetObjectIDByIdent("1_FORMAT", $ParentID));
                $this->SendDebug("ESERA-Temperatur", "1_FORMAT:".$FORMAT, 0);
                if ($data->Value<8500){
                    switch ($FORMAT){
                    case 0:
                        $value = $data->Value;
				        break;
                    case 1:
        			 	$value = $data->Value / 10;
        				break;
                    case 2:
        			 	$value = $data->Value / 100;
        				break;
                    } 
                    SetValue($this->GetIDForIdent("Temperatur"), $value);
                }
                else{
                    //Fehlerhafte ▄bertragung
                    IPS_LogMessage('ESERA-Temperatur', "▄bertragungsfehler erkannt. DeviceNumber: ".$data->DeviceNumber." ,DataPoint: ".$data->DataPoint." ,Value: ".$data->Value);
                    $this->SendDebug("ESERA-Temperatur", "▄bertragungsfehler erkannt");
                }
            }
        }
    }
    private function CreateVariableProfile($ProfileName, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon) {
		    if (!IPS_VariableProfileExists($ProfileName)) {
			       IPS_CreateVariableProfile($ProfileName, $ProfileType);
			       IPS_SetVariableProfileText($ProfileName, "", $Suffix);
			       IPS_SetVariableProfileValues($ProfileName, $MinValue, $MaxValue, $StepSize);
			       IPS_SetVariableProfileDigits($ProfileName, $Digits);
			       IPS_SetVariableProfileIcon($ProfileName, $Icon);
		    }
	  }
}
?>
