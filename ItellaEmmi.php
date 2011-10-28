<?php

/**
 * ItellaEmmi class
 * Used to query the EMMi SOAP service
 */
class ItellaEmmi {

  protected $client; // SOAP client
  protected $service_id; // Used service ID
  protected $username; // Username
  protected $password; // Password
  protected $session_id; // Unique session ID

  protected $service_uri; // URL of the service wsdl
  protected $wsdl_uri;
  protected $file_download_uri; // File download uri
  protected $conversion_id; // Used conversion ID


  // Fixed path to the file download service
  const FILE_PATH = "/file/Download.ashx";

  // Some error codes
  const FILE_NOT_FOUND = -1; // File or conversion was not found
  const FILE_WAIT      = -2; // Try again later

  /**
   * Constructor
   * @param string $service_id
   * @param string $username
   * @param string $password
   * @param string $service_uri
   */
  public function __construct($service_id, $username, $password, $service_uri) {
    $this->service_id = $service_id;
    $this->username = $username;
    $this->password = $password;
    $this->service_uri = $service_uri;
    $this->wsdl_uri = $service_uri . "?wsdl";
    $this->file_download_uri =
      substr($this->service_uri, 0, strpos($this->service_uri, "/", strrpos($this->service_uri, "/"))) .
      self::FILE_PATH;

    // Create the SOAP client class
    $this->client = new SoapClient($this->wsdl_uri);

    // Authenticate to the server
    $result = $this->client->AuthenticateService(
      array(
          "serviceId" => $this->service_id,
          "userName" => $this->username,
          "password" => $this->password,
      )
    );

    $this->conversion_id = 0; // original

    // store session_id for further use
    $this->session_id = $result->AuthenticateServiceResult;

  }


  public function report() {
    var_dump($this);
  }


  /**
   * Set the conversion id to be used
   */
  public function setConversionId($conversion_id) {
    $this->conversion_id = $conversion_id;
  }


  /**
   * Get a file by something that is in any field
   * @param string $id
   */
  public function searchFiles(array $criterias) {

    $result = $this->client->SearchFiles(
      array(
        "sessionId" => $this->session_id,
        "serviceId" => $this->service_id,
        "criterias" => $criterias,
      )
    );

    if (isset($result->SearchFilesResult->FileElement)) {
      $result = $result->SearchFilesResult->FileElement;
    } else {
      return array();
    }

    if (!is_array($result)) {
      $result = array($result);
    }

    return $result;

  }



  /**
   * Get given files download uri
   */
  public function getFileDownloadUri($file) {

    $active_version = $file->ActiveVersion;

    if (!$file) {
      return false;
    }

    // Build the URL for the file as described in emmi documentation
    $data = array(
      "a" => "CONVERSION",
      "s" => $this->service_id,
      "fv" => $active_version->Id,
      "coid" => $this->conversion_id,
      "sid" => $this->session_id,
    );

    return $this->file_download_uri . "?" . http_build_query($data);
  }


  /**
   * Download the given file
   * @param object $file
   * @return error code or file contents
   */
  public function downloadFile($file) {

    $uri = $this->getFileDownloadUri($file);

    if (!$uri) {
      return false;
    }

    $name = $file->ActiveVersion->Id . '_' . $this->conversion_id;
    $extension = $file->ActiveVersion->Extension;
    $filename = file_directory_temp() . "/emmi_" . $name . "." . $extension;

    // Some simple caching
    if (file_exists($filename))
      return $filename;

    // User CURL to download the file
    // so that we can get HTTP statuses back
    // and use them to figure out whats going on
    $ch = curl_init();

    curl_setopt_array($ch,
      array(
        CURLOPT_URL => $uri,
        CURLOPT_RETURNTRANSFER => 1
      )
    );

    // Get the contents of the file
    $contents = curl_exec($ch);

    // ...and the return code
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    // ...and act accordingly
    switch ($code) {

      case 404:
         // not found
      case 501: // invalid conversion
        return self::FILE_NOT_FOUND;

      case 591:
        // started
      case 592:
        // waiting
      case 593:
        // in progress
        return self::FILE_WAIT;
    }

    file_put_contents($filename, $contents);

    return $filename;

  }


 /**
   * Get conversions IDs
   * For this to work there must be atleast one file that was modified within
   * a week
   */
  public function getConversionIDs() {

    $files = array();

    $result = array();

    foreach ($files as $file) {

      $conversions = $file->ActiveVersion->Conversions->ConversionOption;

      if (!is_array($conversions)) {
        $conversions = array($conversions);
      }

      // collect results
      foreach ($conversions as $conv) {
        $name = "";

        // Loop through the multilingual names and concat them
        $lang = $conv->Name->ValueItems->MultilingualValueItem;

        if (!is_array($lang)) {
          $lang = array($lang);
        }

        foreach ($lang as $val) {
          $name .= $val->Value . ", ";
        }

        $name = substr($name, 0, -2);

        $result[$conv->Id] = $name;
      }
    }
    return $result;
  }


};


