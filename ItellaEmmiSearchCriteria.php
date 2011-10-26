<?php


/**
 * ItellaEmmiSearchCriteria
 * Used by ItellaEmmi class as query arguments
 */
class ItellaEmmiSearchCriteria {

  public $SearchableField;
  public $PropertyField;
  public $PropertyFieldId;
  public $PropertyFieldType;
  public $StringSearchOption;
  public $StringValues;
  public $NumberValues;
  public $DateTimeValues;
  public $NumberSearchOption;
  public $IncludeSubFolders;
  
  const STRING_OPTION_CONTAINS  = 1;
  const STRING_OPTION_WORDS     = 2;
  const STRING_OPTION_EXACTLY   = 3;
  const STRING_OPTION_BEGINS    = 4;
  const STRING_OPTION_ENDS      = 5;
  const STRING_OPTION_LUCENE    = 6;
  
  const NUMBER_OPTION_BETWEEN = 1;
  const NUMBER_OPTION_EXACTLY = 2;
  const NUMBER_OPTION_LESS    = 3;
  const NUMBER_OPTION_GREATER = 4;
  
  const FIELD_TYPE_SINGLE_LINE_STRING = 1;
  const FIELD_TYPE_MULTI_LINE_STRING  = 2;
  const FIELD_TYPE_RADIO              = 3;
  const FIELD_TYPE_BOOLEAN            = 4;
  const FIELD_TYPE_MULTI_SELECT       = 5;
  const FIELD_TYPE_DROPDOWN           = 7; // skipped 6
  const FIELD_TYPE_TIME_AND_DATE      = 8;
  const FIELD_TYPE_DATE               = 9;
  const FIELD_TYPE_TIME               = 10;
  const FIELD_TYPE_NUMBER             = 12; // skipped 11
  const FIELD_TYPE_CHECKBOX           = 13;

  public function __construct() {
    $this->SearchableField    = false;
    $this->PropertyFieldId    = false;
    $this->PropertyFieldType  = false;
    $this->StringSearchOption = false;
    $this->StringValues       = false;
    $this->NumberValues       = false;
    $this->DateTimeValues     = false;
    $this->NumberSearchOption = false;
    $this->IncludeSubFolders  = false;
  }
  
  public function setStringSearchOption($type = self::STRING_OPTION_CONTAINS) {
    $this->StringSearchOption = $type;
  }
  
  public function setNumberSearchOption($type = self::NUMBER_OPTION_BETWEEN) {
    $this->StringSearchOption = $type;
  }


}



class ItellaEmmiSearchCriteriaKeyword extends ItellaEmmiSearchCriteria {
  public function __construct($value, $field = NULL, $type = self::STRING_OPTION_CONTAINS) {
    parent::__construct();
    if ($field != NULL) {
      $this->SearchableField = 7;
      $this->PropertyFieldId = $field;
      // Note that we assume all fields we want to search TEXT from are actually text fields
      $this->PropertyFieldType = self::FIELD_TYPE_SINGLE_LINE_STRING;
    } else {
      $this->SearchableField = 8;
    }

    $this->StringSearchOption = $type;
    $this->StringValues       = array($value);
  }
}


class ItellaEmmiSearchCriteriaModifiedBetween extends ItellaEmmiSearchCriteria {
   public function __construct($unixtime1, $unixtime2) {
    parent::__construct();
    $this->SearchableField = 13;
    $this->NumberSearchOption = 1;
    $this->DateTimeValues = array(array("UnixTimeStamp" => $unixtime), array("UnixTimeStamp" => $unixtime));
  }
}



class ItellaEmmiSearchCriteriaModifiedBefore extends ItellaEmmiSearchCriteria {
   public function __construct($unixtime) {
    parent::__construct();
    $this->SearchableField = 13;
    $this->NumberSearchOption = 3;
    $this->DateTimeValues = array(array("UnixTimeStamp" => $unixtime));
  }
}



class ItellaEmmiSearchCriteriaModifiedAfter extends ItellaEmmiSearchCriteria {
   public function __construct($unixtime) {
    parent::__construct();
    $this->SearchableField = 13;
    $this->NumberSearchOption = 4;
    $this->DateTimeValues =  array(array("UnixTimeStamp" => $unixtime));
  }
}



class ItellaEmmiSearchCriteriaModifiedExactly extends ItellaEmmiSearchCriteria {
   public function __construct($unixtime) {
    parent::__construct();
    $this->SearchableField = 13;
    $this->NumberSearchOption = 1;
    $this->DateTimeValues = array(array("UnixTimeStamp" => $unixtime));
  }
}



class ItellaEmmiSearchCriteriaAdditionalField extends ItellaEmmiSearchCriteria {
  public function __construct($fieldId = 0, $fieldType = self::FIELD_TYPE_SINGLE_LINE_STRING) {
    parent::__construct();
    $this->SearchableField = 7;
    $this->PropertyFieldId = $fieldId;
    $this->PropertyFieldType = $fieldType;
  }
}



class ItellaEmmiSearchCriteriaCode extends ItellaEmmiSearchCriteriaAdditionalField {
  public function __construct($value, $type = self::STRING_OPTION_CONTAINS) {
    parent::__construct();
    self::setStringSearchOption($type);
    $this->SearchableField = 10;
    $value = is_array($value) ? $value : array($value);
    $this->StringValues = $value;
  }
}



class ItellaEmmiSearchCriteriaColorNr extends ItellaEmmiSearchCriteriaAdditionalField {
  public function __construct($value, $type = self::STRING_OPTION_CONTAINS) {
    parent::__construct();
    self::setStringSearchOption($type);
    $this->SearchableField = 11;
    $value = is_array($value) ? $value : array($value);
    $this->StringValues = $value;
  }
}




class ItellaEmmiSearchCriteriaFilename extends ItellaEmmiSearchCriteria {
   public function __construct($filename, $type = self::STRING_OPTION_CONTAINS) {
    parent::__construct();
    self::setStringSearchOption($type);
    $this->SearchableField = 15;
    $this->StringValues = array($filename);
  }
}
