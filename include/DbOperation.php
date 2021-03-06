<?php

class DbOperation
{
    private $db;
    private $con;

    function __construct() {
        require_once 'include/Constants.php';
        require_once 'include/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }

     public function generateUUID() {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function exportData($new_filepath, $lang) {
        $last_slash = strrpos($new_filepath, "/");
        $filename = substr($new_filepath, $last_slash + 1);
        $current_filepath = TEMP_DIRECTORY . $filename;
        exec("mysqldump --no-create-info --insert-ignore --skip-comments --skip-add-locks --ignore-table=emr_db.admin_user --ignore-table=emr_db.settings -h " . DB_HOST . " -u " . DB_USERNAME .  " --password=" . DB_PASSWORD . " " . DB_NAME . " > " . $current_filepath);
        echo exec("/usr/local/bin/emr_export.sh " . $current_filepath . " " . $new_filepath);
      	unlink($current_filepath);
        header("LOCATION: index.php?exportDone=2&lang=" . $lang);
    }

    public function importDataSimple($filepath, $lang) {
      // simplified import function
      // relies on modified export with INSERT IGNORE, no locks, and no admin user or settings tables
      // relies on lack of foreign key constraints so can import tables in any order
      // if import times out or otherwise fails it can be run again
      // to import we simply run all the insert statements in the export

      $last_slash = strrpos($filepath, "/");
      $filename = substr($filepath, $last_slash + 1);
	    $temp_filepath = TEMP_DIRECTORY . $filename;
	    echo exec("/usr/local/bin/emr_import.sh " . $filepath . " " . $temp_filepath);
	    exec("mysql -h " . DB_HOST . " -u " . DB_USERNAME .  " --password=" . DB_PASSWORD . " " . DB_NAME . " < " . $temp_filepath);
      echo 'Import processed. Please reload or wait for automatic browser refresh!';
    }

    public function importData($filepath, $lang) {
	$last_slash = strrpos($filepath, "/");
        $filename = substr($filepath, $last_slash + 1);
	$temp_filepath = TEMP_DIRECTORY . $filename;
	echo exec("/usr/local/bin/emr_import.sh " . $filepath . " " . $temp_filepath);


	$maxRuntime = 8; // less then your max script execution limit
        $deadline = time()+$maxRuntime;
        $progressFilename = TEMP_DIRECTORY . $filename . '_filepointer'; // tmp file for progress
        $consultsProcessedFilename = TEMP_DIRECTORY . $filename . '_consultsProcessed';
        $errorFilename = TEMP_DIRECTORY . $filename . '_error'; // tmp file for erro

        ($fp = fopen($temp_filepath, 'r')) OR die('failed to open file:'.$filename);

        // check for previous error
        if( file_exists($errorFilename) ){
            die('<pre> previous error: '.file_get_contents($errorFilename));
        }

        // activate automatic reload in browser
        echo '<html><head> <meta http-equiv="refresh" content="'.($maxRuntime+2).'"><pre>';

        // go to previous file position
        $filePosition = 0;
        if( file_exists($progressFilename) ){
            $filePosition = file_get_contents($progressFilename);
            fseek($fp, $filePosition);
        }

        $consults_processed = file_exists($consultsProcessedFilename);
        $consults_to_update = [];
        if($consults_processed) {
            $consults_processed_content = file_get_contents($consultsProcessedFilename);
            if(!empty($consults_processed_content)) {
                $consults_to_update = explode($consults_processed_content, ",");
            }
        }
        echo "<br><br><br>CHECK<br><br>";
        if(!$consults_processed) {
            while( $deadline>time() AND ($line=fgets($fp, 1024000)) ){
                if(substr($line,0,2)=='--' OR trim($line)=='' ){
                    continue;
                }

                $query .= $line;
                if( substr(trim($query),-1)==';' ){
                    if(strpos($query, "INSERT INTO") !== false) {
                        $pos1_apos = strpos($query, "`");
                        $pos2_apos = strpos($query, "`", $pos1_apos + 1);
                        $table_name_length = $pos2_apos - $pos1_apos - 1;

                        $table_name = substr($query, $pos1_apos + 1, $table_name_length);

                        if($table_name == "aa_consults") {
                            $pos1_pare = strpos($query, "(");
                            $pos2_pare = strpos($query, "(", $pos1_pare + 1);
                            $insert_part = substr($query, 0, $pos2_pare);
                            $values_substr = substr(substr($query, $pos2_pare), 0, -2);
                            $values_array = preg_split("/\),\s\(/", $values_substr);


                            $consult_insert_rows = "";
                            $consults_to_update_str = "";

                            for($i = 0; $i < sizeof($values_array); $i++) {
                                $value_row = $values_array[$i];
                                $row_array = explode(", ", $value_row);

                                $id = "";
                                if($i == 0) {
                                    $value_row = $value_row . ")";
                                    $id = substr($row_array[0], 2, 36);
                                } else if ($i == (sizeof($values_array) - 1)) {
                                    $value_row = "(" . $value_row;
                                    $id = substr($row_array[0], 1, 36);
                                } else {
                                    $value_row = "(" . $value_row . ")";
                                    $id = substr($row_array[0], 1, 36);
                                }
                                $datetime_last_updated = $row_array[1];

                                $should_insert = $this->shouldImportRow($table_name, $id, $datetime_last_update, true);
                                if($should_insert) {
                                    $consult_insert_rows .= $value_row . ", ";
                                    $consults_to_update_str .= $id . ",";
                                }
                            }

                            if(!empty($consult_insert_rows)) {
                                $consult_insert_rows = substr($patient_insert_rows, 0, -2);
                                mysqli_query($this->con, $insert_part . $consult_insert_rows);
                            }

                            $consults_processed = true;
                            file_put_contents($consultsProcessedFilename, $consults_to_update_str);
                            if(!empty($consults_to_update_str)) {
                                $consults_to_update_str = substr($consults_to_update_str, 0, -1);
                                $consults_to_update = explode($consults_to_update_str, ",");
                            }
                            break;
                        }
                    }
                }
            }
            rewind($fp);
        }

        $queryCount = 0;
        $query = '';
        while( $deadline>time() AND ($line=fgets($fp, 1024000)) ){
            if(substr($line,0,2)=='--' OR trim($line)=='' ){
                continue;
            }

            $query .= $line;
            if(substr(trim($query),-1)==';' ){
                if(strpos($query, "INSERT INTO") !== false) {
                    $pos1_apos = strpos($query, "`");
                    $pos2_apos = strpos($query, "`", $pos1_apos + 1);
                    $table_name_length = $pos2_apos - $pos1_apos - 1;

                    $table_name = substr($query, $pos1_apos + 1, $table_name_length);

                    $pos1_pare = strpos($query, "(");
                    $pos2_pare = strpos($query, "(", $pos1_pare + 1);
                    $insert_part = substr($query, 0, $pos2_pare);
                    $values_substr = substr(substr($query, $pos2_pare), 0, -2);
                    $values_array = preg_split("/\),\s\(/", $values_substr);

                    if($table_name == 'admin_user') { //DONE
                        $admin_user_insert_rows = "";
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $row_array = explode(", ", $value_row);

                            $id = "";
                            if($i == 0) {
                                $value_row = $value_row . ")";
                                $id = substr($row_array[0], 2, 36);
                            } else if ($i == (sizeof($values_array) - 1)) {
                                $value_row = "(" . $value_row;
                                $id = substr($row_array[0], 1, 36);
                            } else {
                                $value_row = "(" . $value_row . ")";
                                $id = substr($row_array[0], 1, 36);
                            }

                            if(!$this->rowExists($table_name, $id)) {
                                $admin_user_insert_rows .= $value_row . ", ";
                            }
                        }
                        if(!empty($admin_user_insert_rows)) {
                            $admin_user_insert_rows = substr($admin_user_insert_rows, 0, -2);
                            mysqli_query($this->con, $insert_part . $admin_user_insert_rows);
                        }
                    } else if ($table_name == "communities") { //DONE
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $name = "";
                            if($i == 0) {
                                $name = substr($value_row, 2, -1);
                            } else if ($i == sizeof($values_array) - 1) {
                                $name = substr($value_row, 1, -2);
                            } else {
                                $name = substr($value_row, 1, -1);
                            }
                            $name = strval($name);
                            $this->createCommunity($name);
                        }
                    } else if ($table_name == "messages") { //DONE
                        $message_insert_rows = "";
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $row_array = explode(", ", $value_row);

                            $id = "";
                            if($i == 0) {
                                $value_row = $value_row . ")";
                                $id = substr($row_array[0], 2, 36);
                            } else if ($i == (sizeof($values_array) - 1)) {
                                $value_row = "(" . $value_row;
                                $id = substr($row_array[0], 1, 36);
                            } else {
                                $value_row = "(" . $value_row . ")";
                                $id = substr($row_array[0], 1, 36);
                            }
                            $datetime_last_updated = $row_array[1];

                            $should_insert = $this->shouldImportRow($table_name, $id, $datetime_last_update, true);
                            if($should_insert) {
                                $message_insert_rows .= $value_row . ", ";
                            }
                        }
                        if(!empty($message_insert_rows)) {
                            $message_insert_rows = substr($message_insert_rows, 0, -2);
                            mysqli_query($this->con, $insert_part . $message_insert_rows);
                        }
                    } else if ($table_name == "patients") {
                        $patient_insert_rows = "";
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $row_array = explode(", ", $value_row);

                            $id = "";
                            if($i == 0) {
                                $value_row = $value_row . ")";
                                $id = substr($row_array[0], 2, 36);
                            } else if ($i == (sizeof($values_array) - 1)) {
                                $value_row = "(" . $value_row;
                                $id = substr($row_array[0], 1, 36);
                            } else {
                                $value_row = "(" . $value_row . ")";
                                $id = substr($row_array[0], 1, 36);
                            }
                            $datetime_last_updated = $row_array[1];

                            $should_insert = $this->shouldImportRow($table_name, $id, $datetime_last_update, true);
                            if($should_insert) {
                                $patient_insert_rows .= $value_row . ", ";
                            }
                        }
                        if(!empty($patient_insert_rows)) {
                            $patient_insert_rows = substr($patient_insert_rows, 0, -2);
                            mysqli_query($this->con, $insert_part . $patient_insert_rows);
                        }
                    } else if ($table_name == "history_allergies") {
                        $history_allergies_insert_rows = "";
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $row_array = explode(", ", $value_row);

                            $id = "";
                            if($i == 0) {
                                $value_row = $value_row . ")";
                                $id = substr($row_array[0], 2, 36);
                            } else if ($i == (sizeof($values_array) - 1)) {
                                $value_row = "(" . $value_row;
                                $id = substr($row_array[0], 1, 36);
                            } else {
                                $value_row = "(" . $value_row . ")";
                                $id = substr($row_array[0], 1, 36);
                            }
                            $datetime_last_updated = $row_array[1];

                            $should_insert = $this->shouldImportRow($table_name, $id, $datetime_last_update, true);
                            if($should_insert) {
                                $history_allergies_insert_rows .= $value_row . ", ";
                            }
                        }
                        if(!empty($history_allergies_insert_rows)) {
                            $history_allergies_insert_rows = substr($history_allergies_insert_rows, 0, -2);
                            mysqli_query($this->con, $insert_part . $history_allergies_insert_rows);
                        }
                    } else if ($table_name == "history_surgeries") {
                        $history_surgeries_insert_rows = "";
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $row_array = explode(", ", $value_row);

                            $id = "";
                            if($i == 0) {
                                $value_row = $value_row . ")";
                                $id = substr($row_array[0], 2, 36);
                            } else if ($i == (sizeof($values_array) - 1)) {
                                $value_row = "(" . $value_row;
                                $id = substr($row_array[0], 1, 36);
                            } else {
                                $value_row = "(" . $value_row . ")";
                                $id = substr($row_array[0], 1, 36);
                            }
                            $datetime_last_updated = $row_array[1];

                            $should_insert = $this->shouldImportRow($table_name, $id, $datetime_last_update, true);
                            if($should_insert) {
                                $history_surgeries_insert_rows .= $value_row . ", ";
                            }
                        }
                        if(!empty($history_surgeries_insert_rows)) {
                            $history_surgeries_insert_rows = substr($history_surgeries_insert_rows, 0, -2);
                            mysqli_query($this->con, $insert_part . $history_surgeries_insert_rows);
                        }
                    } else if ($table_name == "history_medications") {
                        $history_medications_insert_rows = "";
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $row_array = explode(", ", $value_row);

                            $id = "";
                            if($i == 0) {
                                $value_row = $value_row . ")";
                                $id = substr($row_array[0], 2, 36);
                            } else if ($i == (sizeof($values_array) - 1)) {
                                $value_row = "(" . $value_row;
                                $id = substr($row_array[0], 1, 36);
                            } else {
                                $value_row = "(" . $value_row . ")";
                                $id = substr($row_array[0], 1, 36);
                            }
                            $datetime_last_updated = $row_array[1];

                            $consult_id = $row_array[2];
                            if(!empty($consult_id)) {
                                $consult_id = substr($consult_id, 1, -1);
                            }

                            if(!empty($consult_id)) {
                                if(in_array($consult_id, $consults_to_update)) {
                                    $history_medications_insert_rows .= $value_row . ", ";
                                }
                            } else {
                                $should_insert = $this->shouldImportRow($table_name, $id, $datetime_last_update, true);
                                if($should_insert) {
                                    $history_medications_insert_rows .= $value_row . ", ";
                                }
                            }
                        }
                        if(!empty($history_medications_insert_rows)) {
                            $history_medications_insert_rows = substr($history_medications_insert_rows, 0, -2);
                            mysqli_query($this->con, $insert_part . $history_medications_insert_rows);
                        }
                    } else if ($table_name == "diagnoses_conditions_illnesses") { //
                        $diagnoses_conditions_illnesses_insert_rows = "";
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $row_array = explode(", ", $value_row);

                            $id = "";
                            if($i == 0) {
                                $value_row = $value_row . ")";
                                $id = substr($row_array[0], 2, 36);
                            } else if ($i == (sizeof($values_array) - 1)) {
                                $value_row = "(" . $value_row;
                                $id = substr($row_array[0], 1, 36);
                            } else {
                                $value_row = "(" . $value_row . ")";
                                $id = substr($row_array[0], 1, 36);
                            }
                            $datetime_last_updated = $row_array[1];

                            $consult_id = $row_array[2];
                            if(!empty($consult_id)) {
                                $consult_id = substr($consult_id, 1, -1);
                            }

                            if(!empty($consult_id)) {
                                if(in_array($consult_id, $consults_to_update)) {
                                    $diagnoses_conditions_illnesses .= $value_row . ", ";
                                }
                            } else {
                                $should_insert = $this->shouldImportRow($table_name, $id, $datetime_last_update, true);
                                if($should_insert) {
                                    $diagnoses_conditions_illnesses .= $value_row . ", ";
                                }
                            }
                        }
                        if(!empty($diagnoses_conditions_illnesses)) {
                            $diagnoses_conditions_illnesses = substr($diagnoses_conditions_illnesses, 0, -2);
                            mysqli_query($this->con, $insert_part . $diagnoses_conditions_illnesses);
                        }
                    } else if ($table_name != "settings") { //CONSULT CONTENTS
                        $consult_stuff_insert_rows = "";
                        for($i = 0; $i < sizeof($values_array); $i++) {
                            $value_row = $values_array[$i];
                            $row_array = explode(", ", $value_row);

                            $id = "";
                            if($i == 0) {
                                $value_row = $value_row . ")";
                                $id = substr($row_array[0], 2, 36);
                            } else if ($i == (sizeof($values_array) - 1)) {
                                $value_row = "(" . $value_row;
                                $id = substr($row_array[0], 1, 36);
                            } else {
                                $value_row = "(" . $value_row . ")";
                                $id = substr($row_array[0], 1, 36);
                            }
                            $datetime_last_updated = $row_array[1];

                            $consult_id = $row_array[2];
                            if(!empty($consult_id)) {
                                $consult_id = substr($consult_id, 1, -1);
                            }

                            if(!empty($consult_id)) {
                                if(in_array($consult_id, $consults_to_update)) {
                                    $consult_stuff_insert_rows .= $value_row . ", ";
                                }
                            }
                        }
                        if(!empty($consult_stuff_insert_rows)) {
                            $consult_stuff_insert_rows = substr($consult_stuff_insert_rows, 0, -2);
                            mysqli_query($this->con, $insert_part . $consult_stuff_insert_rows);
                        }
                    }
                }
                $query = '';
                file_put_contents($progressFilename, ftell($fp)); // save the current file position for
                $queryCount++;
            }
        }

        if( feof($fp) ){
            fclose($fp);
	    unlink($temp_filepath);
            unlink($progressFilename);
            unlink($consultsProcessedFilename);
            header("LOCATION: index.php?importDone=2&lang=" . $lang);
        }else{
            echo ftell($fp).'/'.filesize($filename).' '.(round(ftell($fp)/filesize($filename), 2)*100).'%'."\n";
            echo $queryCount.' queries processed! please reload or wait for automatic browser refresh!';
        }

    }

    function shouldImportRow($table_name, $id, $datetime_last_updated, $delete_existing) {
        $stmt = $this->con->prepare("SELECT datetime_last_updated FROM " . $table_name . " WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        if($num_rows > 0) {
            $stmt = $this->con->prepare("SELECT datetime_last_updated FROM " . $table_name . " WHERE id = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $datetime_last_updated = strval(substr($datetime_last_updated, 1, -1));
            $date = $row['datetime_last_updated'];
            if($datetime_last_updated > $date) {
                $this->deleteRow($table_name, $id);
                return true;
            } else {
                return false;
            }
        } else {
            $stmt->close();
            return true;
        }
    }

    function rowExists($table_name, $id) {
        $stmt = $this->con->prepare("SELECT id FROM " . $table_name . " WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }



    function deleteRow($table_name, $id) {
        $stmt = $this->con->prepare("DELETE FROM " . $table_name . " WHERE id = ?");
        $stmt->bind_param("s", $id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();

        if($table_name == "aa_consults") {
            $this->deleteConsultContents($id);
        }
    }

    function deleteConsultContents($id) {
        $this->deleteConsultRows("chief_complaints", $id);
        $this->deleteConsultRows("diagnoses_conditions_illnesses", $id);
        $this->deleteConsultRows("exams", $id);
        $this->deleteConsultRows("followups", $id);
        $this->deleteConsultRows("history_medications", $id);
        $this->deleteConsultRows("hpi_general", $id);
        $this->deleteConsultRows("hpi_pregnancy", $id);
        $this->deleteConsultRows("measurements", $id);
        $this->deleteConsultRows("treatments", $id);
    }

    function deleteConsultRows($table_name, $consult_id) {
        $stmt = $this->con->prepare("DELETE FROM " . $table_name . " WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->close();
    }

    public function close() {
        $this->con->close();
    }

    public function usersExist() {
        $stmt = $this->con->prepare("SELECT id from admin_user");
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function usernameExists($username) {
        $stmt = $this->con->prepare("SELECT id from admin_user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getUsers() {
        $stmt = $this->con->prepare("SELECT * from admin_user");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function deleteUser($id) {
        $stmt = $this->con->prepare("DELETE FROM admin_user WHERE id = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function getUserId($username, $password) {
        $stmt = $this->con->prepare("SELECT id FROM admin_user WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['id'];
    }

    public function validateLogin($username, $password) {
        $stmt = $this->con->prepare("SELECT id FROM admin_user WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function createUser($name, $username, $password) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $username = preg_replace("/\),\s\(/", "], [", $username);
        $password = preg_replace("/\),\s\(/", "], [", $password);

        $id = $this->generateUUID();
        $stmt = $this->con->prepare("INSERT INTO admin_user(id, name, username, password) values(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $id, $name, $username, $password);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function insertBaseCommunities() {
        foreach (BASE_COMMUNITIES as $community_name) {
            $this->createCommunity($community_name);
        }
    }

    public function getGroupMapping() {
        $main_array = [];
        $existing_medical_groups = $this->getExistingMedicalGroups();
        foreach($existing_medical_groups as $medical_group) {
            $medical_group_name = $medical_group['medical_group'];
            if(!empty($medical_group_name)) {
                $inner_array = [];
                $existing_chief_physicians = $this->getExistingChiefPhysicians($medical_group_name);
                foreach($existing_chief_physicians as $chief_physician) {
                    $chief_physician_name = $chief_physician['chief_physician'];
                    if(!empty($chief_physician_name)) {
                        array_push($inner_array, $chief_physician_name);
                    }
                }
                $main_array[$medical_group_name] = $inner_array;
            }
        }
        return $main_array;
    }

    public function getGroupMapping2() {
        $main_array = [];
        $existing_medical_groups = $this->getExistingMedicalGroups();
        foreach($existing_medical_groups as $medical_group) {
            $medical_group_name = $medical_group['medical_group'];
            if(!empty($medical_group_name)) {
                $inner_array = [];
                $existing_signing_physicians = $this->getExistingSigningPhysicians($medical_group_name);
                foreach($existing_signing_physicians as $signing_physician) {
                    $signing_physician_name = $signing_physician['signing_physician'];
                    if(!empty($signing_physician_name)) {
                        array_push($inner_array, $signing_physician_name);
                    }
                }
                $main_array[$medical_group_name] = $inner_array;
            }
        }
        return $main_array;
    }

    public function getExistingMedicalGroups() {
        $stmt = $this->con->prepare("SELECT DISTINCT medical_group FROM aa_consults WHERE medical_group IS NOT NULL ORDER BY medical_group");
        $stmt->execute();
        $medical_groups = $stmt->get_result();
        $stmt->close();
        return $medical_groups;
    }

    public function getExistingChiefPhysicians($medical_group) {
        $stmt = $this->con->prepare("SELECT DISTINCT chief_physician FROM aa_consults WHERE medical_group = ? AND chief_physician IS NOT NULL ORDER BY chief_physician");
        $stmt->bind_param("s", $medical_group);
        $stmt->execute();
        $chief_physicians = $stmt->get_result();
        $stmt->close();
        return $chief_physicians;
    }

    public function getExistingSigningPhysicians($medical_group) {
        $stmt = $this->con->prepare("SELECT DISTINCT signing_physician FROM aa_consults WHERE medical_group = ? AND signing_physician IS NOT NULL ORDER BY signing_physician");
        $stmt->bind_param("s", $medical_group);
        $stmt->execute();
        $signing_physicians = $stmt->get_result();
        $stmt->close();
        return $signing_physicians;
    }

    public function getExistingConsultLocations() {
        $stmt = $this->con->prepare("SELECT DISTINCT location FROM aa_consults WHERE location IS NOT NULL ORDER BY location");
        $stmt->execute();
        $locations = $stmt->get_result();
        $stmt->close();
        return $locations;
    }

    public function settingsExist() {
        $stmt = $this->con->prepare("SELECT id from settings");
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getSettings() {
        if(!$this->settingsExist()) {
            $this->createSettings();
        }
        $stmt = $this->con->prepare("SELECT * FROM settings");
        $stmt->execute();
        $settings = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $settings;
    }

    public function createSettings() {
        $stmt = $this->con->prepare("INSERT INTO settings() values()");
        $result = $stmt->execute();
        $stmt->close();
    }

    public function updateSettings($field, $value) {
        $value = preg_replace("/\),\s\(/", "], [", $value);

        if(!$this->settingsExist()) {
            $this->createSettings();
        }
        $stmt = $this->con->prepare("UPDATE settings SET " . $field . " = ?");
        $stmt->bind_param("s", $value);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function updateMainSettings($default_consult_location, $default_consult_medical_group, $default_consult_chief_physician) {
        $default_consult_location = preg_replace("/\),\s\(/", "], [", $default_consult_location);
        $default_consult_medical_group = preg_replace("/\),\s\(/", "], [", $default_consult_medical_group);
        $default_consult_chief_physician = preg_replace("/\),\s\(/", "], [", $default_consult_chief_physician);

        if(!$this->settingsExist()) {
            $this->createSettings();
        }
        $stmt = $this->con->prepare("UPDATE settings SET default_consult_location = ?, default_consult_medical_group = ?, default_consult_chief_physician = ?");
        $stmt->bind_param("sss", $default_consult_location, $default_consult_medical_group, $default_consult_chief_physician);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function hasPatientsWithCompletedConsultToday() {
        $current_date = Utilities::getCurrentDate();
        $consult_status_complete = CONSULT_STATUS_CONSULT_COMPLETED;
        $stmt = $this->con->prepare("SELECT id FROM patients WHERE consult_status = ? AND consult_status_datetime > ?");
        $stmt->bind_param("ss", $consult_status_complete, $current_date);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getPatientsWithCompletedConsultToday() {
        $current_date = Utilities::getCurrentDate();
        $consult_status_complete = CONSULT_STATUS_CONSULT_COMPLETED;
        $stmt = $this->con->prepare("SELECT * FROM patients WHERE consult_status = ? AND consult_status_datetime > ? ORDER BY lower(name)");
        $stmt->bind_param("ss", $consult_status_complete, $current_date);
        $stmt->execute();
        $patients = $stmt->get_result();
        $stmt->close();
        return $patients;
    }

    public function isTableEmpty($table) {
        $stmt = $this->con->prepare("SELECT * from " . $table);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows == 0;
    }

    public function getCommunities(){
        $stmt = $this->con->prepare("SELECT * FROM communities ORDER BY LOWER(name)");
        $stmt->execute();
        $communities = $stmt->get_result();
        $stmt->close();
        return $communities;
    }

    public function communityExists($name) {
        $stmt = $this->con->prepare("SELECT * from communities WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function createCommunity($name) {
        $name = preg_replace("/\),\s\(/", "], [", $name);

        if(!$this->CommunityExists($name)) {
            $stmt = $this->con->prepare("INSERT INTO communities(name) values(?)");
            $stmt->bind_param("s", $name);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return COMMUNITY_CREATE_SUCCESS;
            } else {
                return COMMUNITY_CREATE_FAILURE;
            }
        } else {
            return COMMUNITY_CREATE_ALREADY_EXISTS;
        }
    }

    public function getAllPatients() {
        $stmt = $this->con->prepare("SELECT * FROM patients ORDER BY LOWER(name)");
        $stmt->execute();
        $patients = $stmt->get_result();
        $stmt->close();
        return $patients;
    }

    public function getPatientsInCommunity($community_name) {
        $stmt = $this->con->prepare("SELECT * FROM patients WHERE community_name = ? ORDER BY LOWER(name)");
        $stmt->bind_param("s", $community_name);
        $stmt->execute();
        $patients = $stmt->get_result();
        $stmt->close();
        return $patients;
    }

    public function patientExists($patient_id) {
        $stmt = $this->con->prepare("SELECT * from patients WHERE id = ?");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getPatient($patient_id) {
        $stmt = $this->con->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $patient = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $patient;
    }

    //SAME EXACT NAME
    //SAME SEX AND DATE OF BIRTH
    public function hasSimilarPatients($name, $sex, $exact_date_of_birth_known, $date_of_birth) {
        if($exact_date_of_birth_known == BOOLEAN_TRUE) {
            $stmt = $this->con->prepare("SELECT id from patients WHERE name LIKE ? OR (sex = ? AND exact_date_of_birth_known = ? AND date_of_birth = ?)");
            $stmt->bind_param("ssss", $name, $sex, $exact_date_of_birth_known, $date_of_birth);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
            return $num_rows > 0;
        } else {
            $stmt = $this->con->prepare("SELECT id from patients WHERE name LIKE ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
            return $num_rows > 0;
        }
    }

    public function getSimilarPatients($name, $sex, $exact_date_of_birth_known, $date_of_birth) {
        if($exact_date_of_birth_known == BOOLEAN_TRUE) {
            $stmt = $this->con->prepare("SELECT * from patients WHERE name LIKE ? OR (sex = ? AND exact_date_of_birth_known = ? AND  date_of_birth = ?) ORDER BY LOWER(name)");
            $stmt->bind_param("ssss", $name, $sex, $exact_date_of_birth_known, $date_of_birth);
            $stmt->execute();
            $similar_patients = $stmt->get_result();
            $stmt->close();
            return $similar_patients;
        } else {
            $stmt = $this->con->prepare("SELECT * from patients WHERE name LIKE ? ORDER BY LOWER(name)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $similar_patients = $stmt->get_result();
            $stmt->close();
            return $similar_patients;
        }
    }

    public function createPatient($name, $community_name, $sex, $exact_date_of_birth_known, $date_of_birth, $datetime) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $community_name = preg_replace("/\),\s\(/", "], [", $community_name);

        $name = trim($name);
        $community_name = trim($community_name);

        $id = $this->generateUUID();

        $this->createCommunity($community_name);
        $stmt = $this->con->prepare("INSERT INTO patients(id, name, community_name, sex, exact_date_of_birth_known, date_of_birth, datetime_registered, datetime_last_updated) values(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $id, $name, $community_name, $sex, $exact_date_of_birth_known, $date_of_birth, $datetime, $datetime);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $id;
        } else {
            return INVALID_VALUE;
        }
    }

    public function updatePatient($patient_id, $name, $community_name, $sex, $exact_date_of_birth_known, $date_of_birth, $datetime) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $community_name = preg_replace("/\),\s\(/", "], [", $community_name);

        $name = trim($name);
        $community_name = trim($community_name);

        $this->createCommunity($community_name);
        $stmt = $this->con->prepare("UPDATE patients SET name = ?, community_name = ?, sex = ?, exact_date_of_birth_known = ?, date_of_birth = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("sssssss", $name, $community_name, $sex, $exact_date_of_birth_known, $date_of_birth, $datetime, $patient_id);
        $result = $stmt->execute();
        $stmt->close();
        return $patient_id;
    }

    public function updatePatientConsultStatus($patient_id, $consult_status, $consult_status_datetime) {
        $current_datetime = Utilities::getCurrentDateTime();
        $stmt = $this->con->prepare("UPDATE patients SET consult_status = ?, consult_status_datetime = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("ssss", $consult_status, $consult_status_datetime, $current_datetime, $patient_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function hasPatientsWithConsultStatus($consult_status) {
        $stmt = $this->con->prepare("SELECT id FROM patients WHERE consult_status = ?");
        $stmt->bind_param("s", $consult_status);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getPatientsByConsultStatus($consult_status) {
        $stmt = $this->con->prepare("SELECT * FROM patients WHERE consult_status = ? ORDER BY consult_status_datetime ASC");
        $stmt->bind_param("s", $consult_status);
        $stmt->execute();
        $patients = $stmt->get_result();
        $stmt->close();
        return $patients;
    }

    public function searchAllPatients($search_str) {
        $text = '%' . $search_str . '%';
        $stmt = $this->con->prepare("SELECT * FROM patients WHERE name LIKE ? ORDER BY LOWER(name)");
        $stmt->bind_param("s", $text);
        $stmt->execute();
        $patients = $stmt->get_result();
        $stmt->close();
        return $patients;
    }

    public function searchPatientsByCommunity($search_str, $community_name) {
        $text = '%' . $search_str . '%';
        $stmt = $this->con->prepare("SELECT * FROM patients WHERE name LIKE ? AND community_name = ? ORDER BY LOWER(name)");
        $stmt->bind_param("ss", $text, $community_name);
        $stmt->execute();
        $patients = $stmt->get_result();
        $stmt->close();
        return $patients;
    }

    public function searchPatientsNotInCommunity($search_str, $community_name) {
        $text = '%' . $search_str . '%';
        $stmt = $this->con->prepare("SELECT * FROM patients WHERE name LIKE ? AND community_name != ? ORDER BY LOWER(name)");
        $stmt->bind_param("ss", $text, $community_name);
        $stmt->execute();
        $patients = $stmt->get_result();
        $stmt->close();
        return $patients;
    }

    public function searchPatientsByConsultStatus($search_str, $consult_status) {
        $text = '%' . $search_str . '%';
        if($consult_status == CONSULT_STATUS_CONSULT_COMPLETED) {
            $current_date = Utilities::getCurrentDate();
            $stmt = $this->con->prepare("SELECT * FROM patients WHERE name LIKE ? AND consult_status = ? AND consult_status_datetime > ? ORDER BY LOWER(name)");
            $stmt->bind_param("sss", $text, $consult_status, $current_date);
            $stmt->execute();
            $patients = $stmt->get_result();
            $stmt->close();
            return $patients;
        } else {
            $status1 = 0;
            $status2 = 0;
            if($consult_status == CONSULT_STATUS_READY_FOR_TRIAGE_INTAKE) {
                $status1 = CONSULT_STATUS_READY_FOR_TRIAGE_PENDING;
                $status2 = CONSULT_STATUS_READY_FOR_TRIAGE_IN_PROGRESS;
            } else if ($consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT) {
                $status1 = CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING;
                $status2 = CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS;
            }
            $stmt = $this->con->prepare("SELECT * FROM patients WHERE name LIKE ? AND (consult_status = ? or consult_status = ?) ORDER BY LOWER(name)");
            $stmt->bind_param("sss", $text, $status1, $status2);
            $stmt->execute();
            $patients = $stmt->get_result();
            $stmt->close();
            return $patients;
        }
    }

    public function searchPatientsNotWithConsultStatus($search_str, $consult_status) {
        $text = '%' . $search_str . '%';
        if($consult_status == CONSULT_STATUS_CONSULT_COMPLETED) {
            $current_date = Utilities::getCurrentDate();
            $stmt = $this->con->prepare("SELECT * FROM patients WHERE name LIKE ? AND (consult_status != ? OR (consult_status = ? AND consult_status_datetime < ?)) ORDER BY LOWER(name)");
            $stmt->bind_param("ssss", $text, $consult_status, $consult_status, $current_date);
            $stmt->execute();
            $patients = $stmt->get_result();
            $stmt->close();
            return $patients;
        } else {
            $status1 = 0;
            $status2 = 0;
            if($consult_status == CONSULT_STATUS_READY_FOR_TRIAGE_INTAKE) {
                $status1 = CONSULT_STATUS_READY_FOR_TRIAGE_PENDING;
                $status2 = CONSULT_STATUS_READY_FOR_TRIAGE_IN_PROGRESS;
            } else if ($consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT) {
                $status1 = CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING;
                $status2 = CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS;
            }
            $stmt = $this->con->prepare("SELECT * FROM patients WHERE name LIKE ? AND consult_status != ? AND consult_status != ? ORDER BY LOWER(name)");
            $stmt->bind_param("sss", $text, $status1, $status2);
            $stmt->execute();
            $patients = $stmt->get_result();
            $stmt->close();
            return $patients;
        }
    }

    public function hasPatientActiveMessages($patient_id) {
        $status_active = 1;
        $stmt = $this->con->prepare("SELECT id FROM messages WHERE patient_id = ? AND status = ?");
        $stmt->bind_param("ss", $patient_id, $status_active);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getPatientActiveMessages($patient_id) {
        $status_active = 1;
        $stmt = $this->con->prepare("SELECT * FROM messages WHERE patient_id = ? AND status = ? ORDER BY datetime_created DESC");
        $stmt->bind_param("ss", $patient_id, $status_active);
        $stmt->execute();
        $messages = $stmt->get_result();
        $stmt->close();
        return $messages;
    }

    public function getMessage($id) {
        $stmt = $this->con->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $message = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $message;
    }

    public function hasMessage($id) {
        $stmt = $this->con->prepare("SELECT id FROM messages WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function createNewMessage($message_id, $patient_id, $status, $message, $submitter, $datetime) {
        $message = preg_replace("/\),\s\(/", "], [", $message);
        $submitter = preg_replace("/\),\s\(/", "], [", $submitter);

        if($this->hasMessage($message_id)) {
            $this->updateMessage($message_id, $status, $message, $submitter, $datetime);
        } else {
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO messages(id, patient_id, status, message, submitter, datetime_created, datetime_last_updated) values(?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $id, $patient_id, $status, $message, $submitter, $datetime, $datetime);
            $result = $stmt->execute();
            $stmt->close();
        }
    }

    public function updateMessage($message_id, $status, $message, $submitter, $datetime) {
        $message = preg_replace("/\),\s\(/", "], [", $message);
        $submitter = preg_replace("/\),\s\(/", "], [", $submitter);

        $stmt = $this->con->prepare("UPDATE messages SET status = ?, message = ?, submitter = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("sssss", $status, $message, $submitter, $datetime, $message_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function deleteMessage($id) {
        $stmt = $this->con->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function startNewConsult($patient_id, $current_datetime, $status) {
        if ($this->hasActiveConsult($patient_id)) {
            return $this->getActiveConsult($patient_id)["id"];
        } else {
            $settings = $this->getSettings();
            $location = "";
            $medical_group = "";
            $chief_physician = "";
            if(isset($settings[SETTINGS_DEFAULT_CONSULT_LOCATION])) {
                $location = $settings[SETTINGS_DEFAULT_CONSULT_LOCATION];
            }
            if(isset($settings[SETTINGS_DEFAULT_CONSULT_MEDICAL_GROUP])) {
                $medical_group = $settings[SETTINGS_DEFAULT_CONSULT_MEDICAL_GROUP];
            }
            if(isset($settings[SETTINGS_DEFAULT_CONSULT_CHIEF_PHYSICIAN])) {
                $chief_physician = $settings[SETTINGS_DEFAULT_CONSULT_CHIEF_PHYSICIAN];
            }

            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO aa_consults(id, patient_id, datetime_started, status, location, medical_group, chief_physician, datetime_last_updated) values(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $id, $patient_id, $current_datetime, $status, $location, $medical_group, $chief_physician, $current_datetime);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                $this->updatePatientConsultStatus($patient_id, $status, $current_datetime);
                return $id;
            } else {
                return -1;
            }
        }
    }

    public function deleteConsult($id, $patient_id) {
        $stmt = $this->con->prepare("DELETE FROM aa_consults WHERE id = ? AND datetime_completed is NULL");
        $stmt->bind_param("s", $id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();

        if($num_rows_deleted == 1) {
            if($this->hasConsults($patient_id)) {
                $recent_consult = $this->getRecentConsult($patient_id);
                if($recent_consult) {
                    $this->updatePatientConsultStatus($patient_id, $recent_consult['status'], $recent_consult['datetime_completed']);
                }
            } else {
                $this->updatePatientConsultStatus($patient_id, 0, NULL);
            }
        }

    }

    public function updateConsult($consult_id, $medical_group, $chief_physician, $signing_physician, $location, $notes, $datetime_completed) {
        $medical_group = preg_replace("/\),\s\(/", "], [", $medical_group);
        $chief_physician = preg_replace("/\),\s\(/", "], [", $chief_physician);
        $signing_physician = preg_replace("/\),\s\(/", "], [", $signing_physician);
        $location = preg_replace("/\),\s\(/", "], [", $location);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE aa_consults SET medical_group = ?, chief_physician = ?, signing_physician = ?, location = ?, notes = ?, datetime_completed = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("ssssssss", $medical_group, $chief_physician, $signing_physician, $location, $notes, $datetime_completed, $datetime_completed, $consult_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function updateConsults() {
        //$current_datetime = Utilities::getCurrentDateTime();
        $old_status = 6;
        $new_status = 5;
        $stmt = $this->con->prepare("UPDATE aa_consults SET status = ? WHERE status = ?");
        $stmt->bind_param("ss", $new_status, $old_status);
        $result = $stmt->execute();
        $stmt->close();

        $stmt = $this->con->prepare("UPDATE patients SET consult_status = ? WHERE consult_status = ?");
        $stmt->bind_param("ss", $new_status, $old_status);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function updateConsultStatus($patient_id, $consult_id, $status) {
        $current_datetime = Utilities::getCurrentDateTime();
        $stmt = $this->con->prepare("UPDATE aa_consults SET status = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("sss", $status, $current_datetime, $consult_id);
        $result = $stmt->execute();
        $stmt->close();
        if($result){
            if($status == 6) {
                $stmt = $this->con->prepare("UPDATE patients SET consult_status = ?, datetime_last_updated = ? WHERE id = ?");
                $stmt->bind_param("sss", $status, $current_datetime, $patient_id);
                $result = $stmt->execute();
                $stmt->close();
            } else {
                $this->updatePatientConsultStatus($patient_id, $status, Utilities::getCurrentDateTime());
            }
        }

    }

    public function getActiveConsult($patient_id) {
        $stmt = $this->con->prepare("SELECT * FROM aa_consults WHERE patient_id = ? AND datetime_completed IS NULL");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $consult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $consult;
    }

    public function getActiveConsultId($patient_id) {
        $stmt = $this->con->prepare("SELECT id FROM aa_consults WHERE patient_id = ? AND datetime_completed IS NULL");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $consult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $consult['id'];
    }

    public function getRecentConsult($patient_id) {
        if($this->hasActiveConsult($patient_id)) {
            return $this->getActiveConsult($patient_id);
        } else if($this->hasConsults($patient_id)) {
            $stmt = $this->con->prepare("SELECT * FROM aa_consults WHERE patient_id = ? ORDER BY datetime_started DESC");
            $stmt->bind_param("s", $patient_id);
            $stmt->execute();
            $recent_consult = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $recent_consult;
        }
        return null;
    }

    public function getRecentConsultId($patient_id) {
        if($this->hasActiveConsult($patient_id)) {
            return $this->getActiveConsultId($patient_id);
        } else if($this->hasConsults($patient_id)) {
            $stmt = $this->con->prepare("SELECT id FROM aa_consults WHERE patient_id = ? ORDER BY datetime_started DESC");
            $stmt->bind_param("s", $patient_id);
            $stmt->execute();
            $recent_consult_id = $stmt->get_result()->fetch_assoc()['id'];
            $stmt->close();
            return $recent_consult_id;
        }
        return -1;
    }

    public function hasActiveConsult($patient_id) {
        $stmt = $this->con->prepare("SELECT id FROM aa_consults WHERE patient_id = ? AND datetime_completed IS NULL");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function hasConsults($patient_id) {
        $stmt = $this->con->prepare("SELECT id FROM aa_consults WHERE patient_id = ?");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getConsults($patient_id) {
        $stmt = $this->con->prepare("SELECT * FROM aa_consults WHERE patient_id = ? ORDER BY datetime_started DESC");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $consults = $stmt->get_result();
        $stmt->close();
        return $consults;
    }

    public function getConsult($id) {
        if($this->hasConsult($id)) {
            $stmt = $this->con->prepare("SELECT * FROM aa_consults WHERE id = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $consult = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $consult;
        } else {
            return null;
        }
    }

    public function hasConsult($id) {
        $stmt = $this->con->prepare("SELECT id FROM aa_consults WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function isConsultComplete($id) {
        if($this->hasConsult($id)) {
            $status = $this->getConsult($id)['status'];
            if($status >= CONSULT_STATUS_CONSULT_COMPLETED) {
                return BOOLEAN_TRUE;
            } else {
                return BOOLEAN_FALSE;
            }
        } else {
            return 0;
        }
    }

    public function getTriageIntakeStatus($consult_id) {
        if($this->consultHasChiefComplaint($consult_id) || $this->consultHasMeasurements($consult_id)) {
            return CONSULT_STATUS_READY_FOR_TRIAGE_IN_PROGRESS;
        } else {
            return CONSULT_STATUS_READY_FOR_TRIAGE_PENDING;
        }
    }

    public function getMedicalConsultStatus($consult_id) {
        if($this->consultHasDiagnosis($consult_id) || $this->consultHasTreatment($consult_id) || $this->consultHasExam($consult_id) || $this->consultHasFollowup($consult_id)) {
            return CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS;
        } else {
            return CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING;
        }
    }

    public function createChiefComplaints($patient_id, $current_consult_status, $consult_id, $type, $text) {
        $text = preg_replace("/\),\s\(/", "], [", $text);

        if($text) {
            if($current_consult_status == CONSULT_STATUS_READY_FOR_TRIAGE_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_TRIAGE_IN_PROGRESS);
            }
            $arr = explode(",", $text);
            foreach($arr as $element) {
                if(Utilities::isDefaultChiefComplaint($element)) {
                    $this->createChiefComplaint($consult_id, $element, "", $type);
                } else {
                    $this->createChiefComplaint($consult_id, INVALID_CHIEF_COMPLAINT, $element, $type);
                }
            }
        }
    }

    public function createChiefComplaint($consult_id, $selected_value, $custom_text, $type) {
        $custom_text = preg_replace("/\),\s\(/", "], [", $custom_text);

        if(!$this->hasMatchingChiefComplaint($consult_id, $selected_value, $custom_text, $type)) {
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO chief_complaints(id, consult_id, selected_value, custom_text, type) values(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $id, $consult_id, $selected_value, $custom_text, $type);
            $result = $stmt->execute();
            $stmt->close();
        }
    }

    public function updateCustomChiefComplaint($chief_complaint_id, $custom_text) {
        $custom_text = preg_replace("/\),\s\(/", "], [", $custom_text);

        $stmt = $this->con->prepare("UPDATE chief_complaints SET custom_text = ? WHERE id = ?");
        $stmt->bind_param("ss", $custom_text, $chief_complaint_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function getAllChiefComplaints($consult_id, $type) {
        $stmt = $this->con->prepare("SELECT * FROM chief_complaints WHERE consult_id = ? AND type = ? ORDER BY selected_value, custom_text");
        $stmt->bind_param("ss", $consult_id, $type);
        $stmt->execute();
        $chief_complaints = $stmt->get_result();
        $stmt->close();
        return $chief_complaints;
    }

    public function hasNonCustomChiefComplaints($consult_id, $type) {
        $invalid_chief_complaint_arg = INVALID_CHIEF_COMPLAINT;
        $stmt = $this->con->prepare("SELECT id FROM chief_complaints WHERE consult_id = ? AND type = ? AND selected_value != ?");
        $stmt->bind_param("sss", $consult_id, $type, $invalid_chief_complaint_arg);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getNonCustomChiefComplaints($consult_id, $type) {
        $invalid_chief_complaint_arg = INVALID_CHIEF_COMPLAINT;
        $stmt = $this->con->prepare("SELECT * FROM chief_complaints WHERE consult_id = ? AND type = ? AND selected_value != ? ORDER BY selected_value");
        $stmt->bind_param("sss", $consult_id, $type, $invalid_chief_complaint_arg);
        $stmt->execute();
        $chief_complaints = $stmt->get_result();
        $stmt->close();
        return $chief_complaints;
    }

    public function getNonCustomChiefComplaintValues($consult_id, $type) {
        $invalid_chief_complaint_arg = INVALID_CHIEF_COMPLAINT;
        $stmt = $this->con->prepare("SELECT selected_value FROM chief_complaints WHERE consult_id = ? AND type = ? AND selected_value != ? ORDER BY selected_value");
        $stmt->bind_param("sss", $consult_id, $type, $invalid_chief_complaint_arg);
        $stmt->execute();
        $chief_complaints = $stmt->get_result();
        $stmt->close();


        $values = [];
        foreach($chief_complaints as $chief_complaint) {
            array_push($values, $chief_complaint['selected_value']);
        }
        return $values;
    }

    public function hasChiefComplaints($consult_id, $type) {
        $stmt = $this->con->prepare("SELECT id FROM chief_complaints WHERE consult_id = ? AND type = ?");
        $stmt->bind_param("ss", $consult_id, $type);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function hasCustomChiefComplaints($consult_id, $type) {
        $invalid_chief_complaint_arg = INVALID_CHIEF_COMPLAINT;
        $stmt = $this->con->prepare("SELECT id FROM chief_complaints WHERE consult_id = ? AND type = ? AND selected_value = ?");
        $stmt->bind_param("sss", $consult_id, $type, $invalid_chief_complaint_arg);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getCustomChiefComplaints($consult_id, $type) {
        $invalid_chief_complaint_arg = INVALID_CHIEF_COMPLAINT;
        $stmt = $this->con->prepare("SELECT * FROM chief_complaints WHERE consult_id = ? AND type = ? AND selected_value = ? ORDER BY custom_text");
        $stmt->bind_param("sss", $consult_id, $type, $invalid_chief_complaint_arg);
        $stmt->execute();
        $chief_complaints = $stmt->get_result();
        $stmt->close();
        return $chief_complaints;
    }

    public function hasMatchingChiefComplaint($consult_id, $selected_value, $custom_text, $type) {
        $invalid_chief_complaint_arg = INVALID_CHIEF_COMPLAINT;
        $stmt = $this->con->prepare("SELECT id FROM chief_complaints WHERE consult_id = ? AND type = ? AND (selected_value = ? OR (selected_value  = ? AND custom_text = ?))");
        $stmt->bind_param("sssss", $consult_id, $type, $selected_value, $invalid_chief_complaint_arg, $custom_text);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function consultHasChiefComplaint($consult_id) {
        $stmt = $this->con->prepare("SELECT id FROM chief_complaints WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function deleteConsultChiefComplaints($consult_id) {
        $stmt = $this->con->prepare("DELETE FROM chief_complaints WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function deleteChiefComplaint($patient_id, $consult_id, $current_consult_status, $id) {
        $stmt = $this->con->prepare("DELETE FROM chief_complaints WHERE id = ?");
        $stmt->bind_param("s", $id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();
        if($num_rows_deleted == 1) {
            $this->deleteHPI($id);
            if($current_consult_status == CONSULT_STATUS_READY_FOR_TRIAGE_IN_PROGRESS) {
                if($this->getTriageIntakeStatus($consult_id) == CONSULT_STATUS_READY_FOR_TRIAGE_PENDING) {
                    $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_TRIAGE_PENDING);
                }
            }
        }
    }

    public function createGeneralHPI($chief_complaint_id, $consult_id, $o_how, $o_cause, $p_provocation, $p_palliation, $q_type, $r_region_main, $r_region_radiates, $s_level, $t_begin_time, $t_before, $t_current, $notes) {
        $o_how = preg_replace("/\),\s\(/", "], [", $o_how);
        $o_cause = preg_replace("/\),\s\(/", "], [", $o_cause);
        $p_provocation = preg_replace("/\),\s\(/", "], [", $p_provocation);
        $p_palliation = preg_replace("/\),\s\(/", "], [", $p_palliation);
        $q_type = preg_replace("/\),\s\(/", "], [", $q_type);
        $r_region_main = preg_replace("/\),\s\(/", "], [", $r_region_main);
        $r_region_radiates = preg_replace("/\),\s\(/", "], [", $r_region_radiates);
        $t_begin_time = preg_replace("/\),\s\(/", "], [", $t_begin_time);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if($this->hasMatchingGeneralHPI($chief_complaint_id)) {
            $this->updateGeneralHPI($chief_complaint_id, $o_how, $o_cause, $p_provocation, $p_palliation, $q_type, $r_region_main, $r_region_radiates, $s_level, $t_begin_time, $t_before, $t_current, $notes);
        } else {
            $stmt = $this->con->prepare("INSERT INTO hpi_general(chief_complaint_id, consult_id, o_how, o_cause, p_provocation, p_palliation, q_type, r_region_main, r_region_radiates, s_level, t_begin_time, t_before, t_current, notes) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssssss", $chief_complaint_id, $consult_id, $o_how, $o_cause, $p_provocation, $p_palliation, $q_type, $r_region_main, $r_region_radiates, $s_level, $t_begin_time, $t_before, $t_current, $notes);
            $result = $stmt->execute();
            $stmt->close();
        }
    }

    public function createPregnancyHPI($chief_complaint_id, $consult_id, $num_weeks_pregnant, $receiving_prenatal_care, $taking_prenatal_vitamins, $received_ultrasound, $num_live_births, $num_miscarriages, $dysuria_urgency_frequency, $abnormal_vaginal_discharge, $vaginal_bleeding, $previous_pregnancy_complications, $complications_notes, $notes) {
        $complications_notes = preg_replace("/\),\s\(/", "], [", $complications_notes);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if($this->hasMatchingPregnancyHPI($chief_complaint_id)) {
            $this->updatePregnancyHPI($chief_complaint_id, $num_weeks_pregnant, $receiving_prenatal_care, $received_ultrasound, $num_live_births, $dysuria_urgency_frequency, $abnormal_vaginal_discharge, $vaginal_bleeding, $previous_pregnancy_complications, $complications_notes, $notes);
        } else {
            $stmt = $this->con->prepare("INSERT INTO hpi_pregnancy(chief_complaint_id, consult_id, num_weeks_pregnant, receiving_prenatal_care, taking_prenatal_vitamins, received_ultrasound, num_live_births, num_miscarriages, dysuria_urgency_frequency, abnormal_vaginal_discharge, vaginal_bleeding, previous_pregnancy_complications, complications_notes, notes) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssssss", $chief_complaint_id, $consult_id, $num_weeks_pregnant, $receiving_prenatal_care, $taking_prenatal_vitamins, $received_ultrasound, $num_live_births, $num_miscarriages, $dysuria_urgency_frequency, $abnormal_vaginal_discharge, $vaginal_bleeding, $previous_pregnancy_complications, $complications_notes, $notes);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function updateGeneralHPI($chief_complaint_id, $o_how, $o_cause, $p_provocation, $p_palliation, $q_type, $r_region_main, $r_region_radiates, $s_level, $t_begin_time, $t_before, $t_current, $notes) {
        $o_how = preg_replace("/\),\s\(/", "], [", $o_how);
        $o_cause = preg_replace("/\),\s\(/", "], [", $o_cause);
        $p_provocation = preg_replace("/\),\s\(/", "], [", $p_provocation);
        $p_palliation = preg_replace("/\),\s\(/", "], [", $p_palliation);
        $q_type = preg_replace("/\),\s\(/", "], [", $q_type);
        $r_region_main = preg_replace("/\),\s\(/", "], [", $r_region_main);
        $r_region_radiates = preg_replace("/\),\s\(/", "], [", $r_region_radiates);
        $t_begin_time = preg_replace("/\),\s\(/", "], [", $t_begin_time);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE hpi_general SET o_how = ?, o_cause = ?, p_provocation = ?, p_palliation = ?, q_type = ?, r_region_main = ?, r_region_radiates = ?, s_level = ?, t_begin_time = ?, t_before = ?, t_current = ?, notes = ? WHERE chief_complaint_id = ?");
        $stmt->bind_param("sssssssssssss", $o_how, $o_cause, $p_provocation, $p_palliation, $q_type, $r_region_main, $r_region_radiates, $s_level, $t_begin_time, $t_before, $t_current, $notes, $chief_complaint_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function updatePregnancyHPI($chief_complaint_id, $num_weeks_pregnant, $receiving_prenatal_care, $taking_prenatal_vitamins, $received_ultrasound, $num_live_births, $num_miscarriages, $dysuria_urgency_frequency, $abnormal_vaginal_discharge, $vaginal_bleeding, $previous_pregnancy_complications, $complications_notes, $notes) {
        $complications_notes = preg_replace("/\),\s\(/", "], [", $complications_notes);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE hpi_pregnancy SET num_weeks_pregnant = ?, receiving_prenatal_care = ?, taking_prenatal_vitamins = ?, received_ultrasound = ?, num_live_births = ?, num_miscarriages = ?, dysuria_urgency_frequency = ?, abnormal_vaginal_discharge = ?, vaginal_bleeding = ?, previous_pregnancy_complications = ?, complications_notes = ?, notes = ? WHERE chief_complaint_id = ?");
        $stmt->bind_param("sssssssssssss", $num_weeks_pregnant, $receiving_prenatal_care, $taking_prenatal_vitamins, $received_ultrasound, $num_live_births, $num_miscarriages, $dysuria_urgency_frequency, $abnormal_vaginal_discharge, $vaginal_bleeding, $previous_pregnancy_complications, $complications_notes, $notes, $chief_complaint_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function getGeneralHPI($chief_complaint_id) {
        $stmt = $this->con->prepare("SELECT * FROM hpi_general WHERE chief_complaint_id = ?");
        $stmt->bind_param("s", $chief_complaint_id);
        $stmt->execute();
        $hpi = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $hpi;
    }

    public function getPregnancyHPI($chief_complaint_id) {
        $stmt = $this->con->prepare("SELECT * FROM hpi_pregnancy WHERE chief_complaint_id = ?");
        $stmt->bind_param("s", $chief_complaint_id);
        $stmt->execute();
        $hpi = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $hpi;
    }

    public function hasMatchingGeneralHPI($chief_complaint_id) {
        $stmt = $this->con->prepare("SELECT chief_complaint_id FROM hpi_general WHERE chief_complaint_id = ?");
        $stmt->bind_param("s", $chief_complaint_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function hasMatchingPregnancyHPI($chief_complaint_id) {
        $stmt = $this->con->prepare("SELECT chief_complaint_id FROM hpi_pregnancy WHERE chief_complaint_id = ?");
        $stmt->bind_param("s", $chief_complaint_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function consultHasHPI($consult_id) {
        $stmt = $this->con->prepare("SELECT chief_complaint_id FROM hpi_general WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        if($num_rows > 0) {
            return true;
        } else {
            $stmt = $this->con->prepare("SELECT chief_complaint_id FROM hpi_pregnancy WHERE consult_id = ?");
            $stmt->bind_param("s", $consult_id);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
            return $num_rows > 0;
        }
    }

    public function deleteConsultHPIs($consult_id) {
        $stmt = $this->con->prepare("DELETE FROM hpi_general WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $result = $stmt->execute();
        $stmt->close();

        $stmt = $this->con->prepare("DELETE FROM hpi_pregnancy WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function deleteHPI($chief_complaint_id) {
        $stmt = $this->con->prepare("DELETE FROM hpi_general WHERE chief_complaint_id = ?");
        $stmt->bind_param("s", $chief_complaint_id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();
        if($num_rows_deleted == 0) {
            $stmt = $this->con->prepare("DELETE FROM hpi_pregnancy WHERE chief_complaint_id = ?");
            $stmt->bind_param("s", $chief_complaint_id);
            $result = $stmt->execute();
            $stmt->close();
        }
    }

    public function deleteGeneralHPI($chief_complaint_id) {
        $stmt = $this->con->prepare("DELETE FROM hpi_general WHERE chief_complaint_id = ?");
        $stmt->bind_param("s", $chief_complaint_id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();
    }

    public function deletePregnancyHPI($chief_complaint_id) {
        $stmt = $this->con->prepare("DELETE FROM hpi_pregnancy WHERE chief_complaint_id = ?");
        $stmt->bind_param("s", $chief_complaint_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function createMeasurements($patient_id, $current_consult_status, $consult_id, $is_pregnant, $date_last_menstruation, $temperature_units, $temperature_value, $blood_pressure_systolic, $blood_pressure_diastolic, $pulse_rate, $blood_oxygen_saturation, $respiration_rate, $height_units, $height_value, $weight_units, $weight_value, $waist_circumference_units, $waist_circumference_value, $notes) {
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if($this->consultHasMeasurements($consult_id)) {
            $this->updateMeasurements($consult_id, $is_pregnant, $date_last_menstruation, $temperature_units, $temperature_value, $blood_pressure_systolic, $blood_pressure_diastolic, $pulse_rate, $blood_oxygen_saturation, $respiration_rate, $height_units, $height_value, $weight_units, $weight_value, $waist_circumference_units, $waist_circumference_value, $notes);
        } else {
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO measurements(id, consult_id, is_pregnant, date_last_menstruation, temperature_units, temperature_value, blood_pressure_systolic, blood_pressure_diastolic, pulse_rate, blood_oxygen_saturation, respiration_rate, height_units, height_value, weight_units, weight_value, waist_circumference_units, waist_circumference_value, notes) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssssssssss", $id, $consult_id, $is_pregnant, $date_last_menstruation, $temperature_units, $temperature_value, $blood_pressure_systolic, $blood_pressure_diastolic, $pulse_rate, $blood_oxygen_saturation, $respiration_rate, $height_units, $height_value, $weight_units, $weight_value, $waist_circumference_units, $waist_circumference_value, $notes);
            $stmt->execute();
            $stmt->close();

            if($current_consult_status == CONSULT_STATUS_READY_FOR_TRIAGE_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_TRIAGE_IN_PROGRESS);
            }
        }
    }

    public function updateMeasurements($consult_id, $is_pregnant, $date_last_menstruation, $temperature_units, $temperature_value, $blood_pressure_systolic, $blood_pressure_diastolic, $pulse_rate, $blood_oxygen_saturation, $respiration_rate, $height_units, $height_value, $weight_units, $weight_value, $waist_circumference_units, $waist_circumference_value, $notes) {
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE measurements SET is_pregnant = ?, date_last_menstruation = ?, temperature_units = ?, temperature_value = ?, blood_pressure_systolic = ?, blood_pressure_diastolic = ?, pulse_rate = ?, blood_oxygen_saturation = ?, respiration_rate = ?, height_units = ?, height_value = ?, weight_units = ?, weight_value = ?, waist_circumference_units = ?, waist_circumference_value = ?, notes = ? WHERE consult_id = ?");
        $stmt->bind_param("sssssssssssssssss", $is_pregnant, $date_last_menstruation, $temperature_units, $temperature_value, $blood_pressure_systolic, $blood_pressure_diastolic, $pulse_rate, $blood_oxygen_saturation, $respiration_rate, $height_units, $height_value, $weight_units, $weight_value, $waist_circumference_units, $waist_circumference_value, $notes, $consult_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function getMeasurements($consult_id) {
        $stmt = $this->con->prepare("SELECT * FROM measurements WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $measurements = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $measurements;
    }

    public function consultHasMeasurements($consult_id) {
        $stmt = $this->con->prepare("SELECT id FROM measurements WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function deleteConsultMeasurements($patient_id, $current_consult_status, $consult_id) {
        $stmt = $this->con->prepare("DELETE FROM measurements WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();

        if($current_consult_status == CONSULT_STATUS_READY_FOR_TRIAGE_IN_PROGRESS) {
            if($this->getTriageIntakeStatus($consult_id) == CONSULT_STATUS_READY_FOR_TRIAGE_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_TRIAGE_PENDING);
            }
        }
    }

    public function createExam($consult_id, $patient_id, $current_consult_status, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4, $information, $options, $other_option, $notes) {
        $information = preg_replace("/\),\s\(/", "], [", $information);
        $other_option = preg_replace("/\),\s\(/", "], [", $other_option);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if($this->hasMatchingExam($consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4)) {
            if($is_normal == BOOLEAN_TRUE) {
                if(!$options) {
                    $this->deleteNullExams($consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4);
                    if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS) {
                        if($this->getMedicalConsultStatus($consult_id) == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS) {
                            $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING);
                        } else if ($this->getMedicalConsultStatus($consult_id) == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
                            $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING);
                        }
                    }
                } else {
                    //FIGURE THIS OUT
                }
            } else {
                //FIGURE THIS OUT
            }
        } else {
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO exams(id, consult_id, is_normal, main_category, arg1, arg2, arg3, arg4, information, options, other_option, notes) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssss", $id, $consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4, $information, $options, $other_option, $notes);
            $stmt->execute();
            $stmt->close();

            if($is_normal == BOOLEAN_TRUE) {
                $this->deleteLowerAbnormalExams($consult_id, $main_category, $arg1, $arg2, $arg3, $arg4);
            } else if ($is_normal == BOOLEAN_FALSE) {
                $this->deleteUpperNormalExams($consult_id, $main_category, $arg1, $arg2, $arg3, $arg4, $information);
            }

            if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS);
            }
        }
    }

    public function deleteUpperNormalExams($consult_id, $main_category, $arg1, $arg2, $arg3, $arg4, $information) {
        if($arg1 == NULL) {
            if($information) {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            }
        } else if($arg2 == NULL) {
            if($information) {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, NULL, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            } else {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            }
        } else if ($arg3 == NULL) {
            if($information) {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, NULL, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            } else {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, NULL, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            }
        } else if ($arg4 == NULL) {
            if($information) {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, $arg3, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, NULL, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            } else {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, NULL, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            }
        } else {
            if($information) {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, $arg3, $arg4);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, $arg3, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, NULL, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            } else {
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, $arg3, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, $arg2, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, $arg1, NULL, NULL, NULL);
                $this->deleteNullExams($consult_id, BOOLEAN_TRUE, $main_category, NULL, NULL, NULL, NULL);
            }
        }
    }

    public function deleteLowerAbnormalExams($consult_id, $main_category, $arg1, $arg2, $arg3, $arg4) {
        if($arg1 == NULL) {
            $this->deleteAllExams($consult_id, BOOLEAN_FALSE, $main_category, NULL, NULL, NULL, NULL);
        } else if ($arg2 == NULL) {
            $this->deleteAllExams($consult_id, BOOLEAN_FALSE, $main_category, $arg1, NULL, NULL, NULL);
        } else if ($arg3 == NULL) {
            $this->deleteAllExams($consult_id, BOOLEAN_FALSE, $main_category, $arg1, $arg2, NULL, NULL);
        } else if ($arg4 == NULL) {
            $this->deleteAllExams($consult_id, BOOLEAN_FALSE, $main_category, $arg1, $arg2, $arg3, NULL);
        } else {
            $this->deleteAllExams($consult_id, BOOLEAN_FALSE, $main_category, $arg1, $arg2, $arg3, $arg4);
        }
    }

    public function deleteNullExams($consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4) {
        $stmt = "";
        if($main_category == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams WHERE consult_id = ? AND is_normal = ? AND main_category is NULL");
            $stmt->bind_param("ss", $consult_id, $is_normal);
        } else if ($arg1 == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 is NULL");
            $stmt->bind_param("sss", $consult_id, $is_normal, $main_category);
        } else if ($arg2 == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 is NULL");
            $stmt->bind_param("ssss", $consult_id, $is_normal, $main_category, $arg1);
        } else if ($arg3 == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ? AND arg3 is NULL");
            $stmt->bind_param("sssss", $consult_id, $is_normal, $main_category, $arg1, $arg2);
        } else if ($arg4 == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ? AND arg3 = ? AND arg4 is NULL");
            $stmt->bind_param("ssssss", $consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3);
        } else {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ? AND arg3 = ? AND arg4 = ?");
            $stmt->bind_param("sssssss", $consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4);
        }
        $stmt->execute();
        $stmt->close();
    }

    public function deleteAllExams($consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4) {
        $stmt = "";
        if($main_category == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams WHERE consult_id = ? is_normal = ?");
            $stmt->bind_param("ss", $consult_id, $is_normal);
        } else if ($arg1 == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ?");
            $stmt->bind_param("sss", $consult_id, $is_normal, $main_category);
        } else if ($arg2 == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ?");
            $stmt->bind_param("ssss", $consult_id, $is_normal, $main_category, $arg1);
        } else if ($arg3 == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ?");
            $stmt->bind_param("sssss", $consult_id, $is_normal, $main_category, $arg1, $arg2);
        } else if ($arg4 == NULL) {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ? AND arg3 = ?");
            $stmt->bind_param("ssssss", $consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3);
        } else {
            $stmt = $this->con->prepare("DELETE FROM exams where consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ? AND arg3 = ? AND arg4 = ?");
            $stmt->bind_param("sssssss", $consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4);
        }
        $stmt->execute();
        $stmt->close();
    }

    public function updateExam($exam_id, $consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4, $information, $options, $other_option, $notes) {
        $stmt = $this->con->prepare("UPDATE exams SET is_normal = ?, main_category = ?, arg1 = ?, arg2 = ?, arg3 = ?, arg4 = ?, information = ?, options = ?, other_option = ?, notes = ? WHERE id = ?");
        $information = preg_replace("/\),\s\(/", "], [", $information);
        $other_option = preg_replace("/\),\s\(/", "], [", $other_option);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt->bind_param("sssssssssss", $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4, $information, $options, $other_option, $notes, $exam_id);
        $result = $stmt->execute();
        $stmt->close();

        if($is_normal == BOOLEAN_TRUE) {
            $this->deleteLowerAbnormalExams($consult_id, $main_category, $arg1, $arg2, $arg3, $arg4);
        } else if ($is_normal == BOOLEAN_FALSE) {
            $this->deleteUpperNormalExams($consult_id, $main_category, $arg1, $arg2, $arg3, $arg4, $information);
        }
    }

    public function getExams($consult_id, $is_normal) {
        $stmt = $this->con->prepare("SELECT * FROM exams WHERE consult_id = ? AND is_normal = ?");
        $stmt->bind_param("ss", $consult_id, $is_normal);
        $stmt->execute();
        $exams = $stmt->get_result();
        $stmt->close();
        return $exams;
    }

    public function getExamsStructured($consult_id, $is_normal) {
        $main_array = [];
        $main_index = 0;
        $exams = $this->getExams($consult_id, $is_normal);
        foreach($exams as $exam) {
            $inner_array = [];
            array_push($inner_array, $exam['id'], $exam['main_category'], $exam['arg1'], $exam['arg2'], $exam['arg3'], $exam['arg4'], $exam['information'], $exam['options'], $exam['other_option'], $exam['notes']);
            $main_array[$main_index++] = $inner_array;
        }
        return $main_array;
    }

    public function hasMatchingExam($consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4) {
        $num_rows = 0;
        if($arg1 == NULL) {
            $stmt = $this->con->prepare("SELECT id FROM exams WHERE consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 is NULL");
            $stmt->bind_param("sss", $consult_id, $is_normal, $main_category);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
        } else if ($arg2 == NULL) {
            $stmt = $this->con->prepare("SELECT id FROM exams WHERE consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 is NULL");
            $stmt->bind_param("ssss", $consult_id, $is_normal, $main_category, $arg1);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
        } else if ($arg3 == NULL) {
            $stmt = $this->con->prepare("SELECT id FROM exams WHERE consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ? AND arg3 is NULL");
            $stmt->bind_param("sssss", $consult_id, $is_normal, $main_category, $arg1, $arg2);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
        } else if ($arg4 == NULL) {
            $stmt = $this->con->prepare("SELECT id FROM exams WHERE consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ? AND arg3 = ? AND arg4 is NULL");
            $stmt->bind_param("ssssss", $consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
        } else {
            $stmt = $this->con->prepare("SELECT id FROM exams WHERE consult_id = ? AND is_normal = ? AND main_category = ? AND arg1 = ? AND arg2 = ? AND arg3 = ? AND arg4 = ?");
            $stmt->bind_param("sssssss", $consult_id, $is_normal, $main_category, $arg1, $arg2, $arg3, $arg4);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
        }
        return $num_rows > 0;
    }

    public function consultHasExam($consult_id) {
        $stmt = $this->con->prepare("SELECT id FROM exams WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function consultHasExams($consult_id, $is_normal) {
        $stmt = $this->con->prepare("SELECT id FROM exams WHERE consult_id = ? AND is_normal = ?");
        $stmt->bind_param("ss", $consult_id, $is_normal);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function deleteConsultExams($consult_id) {
        $stmt = $this->con->prepare("DELETE FROM exams WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();
    }

    public function deleteExam($id, $patient_id, $consult_id, $current_consult_status) {
        $stmt = $this->con->prepare("DELETE FROM exams WHERE id = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();

        if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS) {
            $medical_consult_status = $this->getMedicalConsultStatus($consult_id);
            if ($medical_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING);
            }
        }
    }

    public function createDiagnosis($patient_id, $consult_id, $current_consult_status, $is_chronic, $category, $type, $other, $notes) {
        $other = preg_replace("/\),\s\(/", "], [", $other);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $history_show = BOOLEAN_TRUE;
        $date = Utilities::getCurrentDateTime();
        $id = $this->generateUUID();
        $stmt = $this->con->prepare("INSERT INTO diagnoses_conditions_illnesses(id, patient_id, consult_id, is_chronic, category, type, other, notes, datetime_created, datetime_last_updated, history_show) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $id, $patient_id, $consult_id, $is_chronic, $category, $type, $other, $notes, $date, $date, $history_show);
        $stmt->execute();
        $stmt->close();

        if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
            $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS);
        }
    }

    public function updateDiagnosis($diagnosis_id, $is_chronic, $other, $notes) {
        $other = preg_replace("/\),\s\(/", "], [", $other);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $date = Utilities::getCurrentDateTime();
        $stmt = $this->con->prepare("UPDATE diagnoses_conditions_illnesses SET is_chronic = ?, other = ?, notes = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("sssss", $is_chronic, $other, $notes, $date, $diagnosis_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function getDiagnoses($consult_id) {
        $stmt = $this->con->prepare("SELECT * FROM diagnoses_conditions_illnesses WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $diagnoses = $stmt->get_result();
        $stmt->close();
        return $diagnoses;
    }

    public function getDiagnosis() {

    }

    public function getDiagnosesStructured($consult_id) {
        $main_array = [];
        $main_index = 0;
        $diagnoses = $this->getDiagnoses($consult_id);
        foreach($diagnoses as $diagnosis) {
            $inner_array = [];
            array_push($inner_array, $diagnosis['id'], $diagnosis['is_chronic'], $diagnosis['category'], $diagnosis['type'], $diagnosis['other'], $diagnosis['notes']);
            $main_array[$main_index++] = $inner_array;
        }
        return $main_array;
    }

    public function consultHasDiagnosis($consult_id) {
        $stmt = $this->con->prepare("SELECT id FROM diagnoses_conditions_illnesses WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function deleteConsultDiagnoses($consult_id) {
        $stmt = $this->con->prepare("DELETE FROM diagnoses_conditions_illnesses WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();
    }

    public function deleteDiagnosis($id, $patient_id, $consult_id, $current_consult_status) {
        $stmt = $this->con->prepare("DELETE FROM diagnoses_conditions_illnesses WHERE id = ?");
        $stmt->bind_param("s", $id);
        $num_rows_deleted = $stmt->execute();
        $stmt->close();

        if($num_rows_deleted == 1) {
            $this->deleteTreatmentForDiagnosis($id);
        }

        if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS) {
            $medical_consult_status = $this->getMedicalConsultStatus($consult_id);
            if ($medical_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING);
            }
        }
    }

    public function createTreatment($current_consult_status, $patient_id, $consult_id, $diagnosis_id, $type, $other, $strength, $strength_units, $strength_units_other, $conc_part_one, $conc_part_one_units, $conc_part_one_units_other, $conc_part_two, $conc_part_two_units, $conc_part_two_units_other, $quantity, $quantity_units, $quantity_units_other, $route, $route_other, $prn, $dosage, $dosage_units, $dosage_units_other, $frequency, $frequency_other, $duration, $duration_units, $duration_units_other, $notes, $add_to_medication_history) {
        $other = preg_replace("/\),\s\(/", "], [", $other);
        $strength_units_other = preg_replace("/\),\s\(/", "], [", $strength_units_other);
        $conc_part_one_units_other = preg_replace("/\),\s\(/", "], [", $conc_part_one_units_other);
        $conc_part_two_units_other = preg_replace("/\),\s\(/", "], [", $conc_part_two_units_other);
        $quantity_units_other = preg_replace("/\),\s\(/", "], [", $quantity_units_other);
        $route_other = preg_replace("/\),\s\(/", "], [", $route_other);
        $dosage_units_other = preg_replace("/\),\s\(/", "], [", $dosage_units_other);
        $frequency_other = preg_replace("/\),\s\(/", "], [", $frequency_other);
        $duration_units_other = preg_replace("/\),\s\(/", "], [", $duration_units_other);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $id = $this->generateUUID();
        $stmt = $this->con->prepare("INSERT INTO treatments(id, consult_id, diagnosis_id, type, other, strength, strength_units, strength_units_other, conc_part_one, conc_part_one_units, conc_part_one_units_other, conc_part_two, conc_part_two_units, conc_part_two_units_other, quantity, quantity_units, quantity_units_other, route, route_other, prn, dosage, dosage_units, dosage_units_other, frequency, frequency_other, duration, duration_units, duration_units_other, notes, add_to_medication_history) value(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssssssssssssssssssss", $id, $consult_id, $diagnosis_id, $type, $other, $strength, $strength_units, $strength_units_other, $conc_part_one, $conc_part_one_units, $conc_part_one_units_other, $conc_part_two, $conc_part_two_units, $conc_part_two_units_other, $quantity, $quantity_units, $quantity_units_other, $route, $route_other, $prn, $dosage, $dosage_units, $dosage_units_other, $frequency, $frequency_other, $duration, $duration_units, $duration_units_other, $notes, $add_to_medication_history);
        $result = $stmt->execute();
        $stmt->close();

        if($add_to_medication_history == 2) {
            $this->addMedicationHistory($patient_id, $consult_id, $type, $other, $notes);
        }

        if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
            $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS);
        }

    }

    public function addMedicationHistory($patient_id, $consult_id, $type, $other, $notes) {
        $other = preg_replace("/\),\s\(/", "], [", $other);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $start_date = Utilities::getCurrentDate();
        $end_date = NULL;
        $current_datetime = Utilities::getCurrentDateTime();
        $source = 1;
        $name = $other;
        if(!$name) {
            $name = TREATMENT_MAPPING[$type];
        }
        if(!$this->hasHistoryMedication($consult_id, $name)) {
            $this->createNewHistoryMedication($patient_id, $consult_id, "", $name, $start_date, $end_date, $source, $notes, $current_datetime);
        }
    }

    public function hasHistoryMedication($consult_id, $name) {
        $stmt = $this->con->prepare("SELECT id FROM history_medications WHERE consult_id = ? AND name = ?");
        $stmt->bind_param("ss", $consult_id, $name);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function updateTreatment($patient_id, $consult_id, $treatment_id, $type, $other, $strength, $strength_units, $strength_units_other, $conc_part_one, $conc_part_one_units, $conc_part_one_units_other, $conc_part_two, $conc_part_two_units, $conc_part_two_units_other, $quantity, $quantity_units, $quantity_units_other, $route, $route_other, $prn, $dosage, $dosage_units, $dosage_units_other, $frequency, $frequency_other, $duration, $duration_units, $duration_units_other, $notes, $add_to_medication_history) {
        $other = preg_replace("/\),\s\(/", "], [", $other);
        $strength_units_other = preg_replace("/\),\s\(/", "], [", $strength_units_other);
        $conc_part_one_units_other = preg_replace("/\),\s\(/", "], [", $conc_part_one_units_other);
        $conc_part_two_units_other = preg_replace("/\),\s\(/", "], [", $conc_part_two_units_other);
        $quantity_units_other = preg_replace("/\),\s\(/", "], [", $quantity_units_other);
        $route_other = preg_replace("/\),\s\(/", "], [", $route_other);
        $dosage_units_other = preg_replace("/\),\s\(/", "], [", $dosage_units_other);
        $frequency_other = preg_replace("/\),\s\(/", "], [", $frequency_other);
        $duration_units_other = preg_replace("/\),\s\(/", "], [", $duration_units_other);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE treatments SET other = ?, strength = ?, strength_units = ?, strength_units_other = ?, conc_part_one = ?, conc_part_one_units = ?, conc_part_one_units_other = ?, conc_part_two = ?, conc_part_two_units = ?, conc_part_two_units_other = ?, quantity = ?, quantity_units = ?, quantity_units_other = ?, route = ?, route_other = ?, prn = ?, dosage = ?, dosage_units = ?, dosage_units_other = ?, frequency = ?, frequency_other = ?, duration = ?, duration_units = ?, duration_units_other = ?, notes = ?, add_to_medication_history = ? WHERE id = ?");
        $stmt->bind_param("sssssssssssssssssssssssssss", $other, $strength, $strength_units, $strength_units_other, $conc_part_one, $conc_part_one_units, $conc_part_one_units_other, $conc_part_two, $conc_part_two_units, $conc_part_two_units_other, $quantity, $quantity_units, $quantity_units_other, $route, $route_other, $prn, $dosage, $dosage_units, $dosage_units_other, $frequency, $frequency_other, $duration, $duration_units, $duration_units_other, $notes, $add_to_medication_history, $treatment_id);
        $result = $stmt->execute();
        $stmt->close();

        if($add_to_medication_history == 2) {
            $this->addMedicationHistory($patient_id, $consult_id, $type, $other, $notes);
        }
    }

    public function getTreatmentsStructured($consult_id) {
        $main_array = [];
        $main_index = 0;
        $treatments = $this->getTreatments($consult_id);
        foreach($treatments as $treatment) {
            $inner_array = [];
            array_push($inner_array, $treatment['id'], $treatment['diagnosis_id'], $treatment['type'], $treatment['other'], $treatment['strength'], $treatment['strength_units'], $treatment['strength_units_other'], $treatment['conc_part_one'], $treatment['conc_part_one_units'], $treatment['conc_part_one_units_other'], $treatment['conc_part_two'], $treatment['conc_part_two_units'], $treatment['conc_part_two_units_other'], $treatment['quantity'], $treatment['quantity_units'], $treatment['quantity_units_other'], $treatment['route'], $treatment['route_other'], $treatment['prn'], $treatment['dosage'], $treatment['dosage_units'], $treatment['dosage_units_other'], $treatment['frequency'], $treatment['frequency_other'], $treatment['duration'], $treatment['duration_units'], $treatment['duration_units_other'], $treatment['notes'], $treatment['add_to_medication_history']);
            $main_array[$main_index++] = $inner_array;
        }
        return $main_array;
    }

    public function hasMatchingTreatment() {

    }

    public function getTreatments($consult_id) {
        $stmt = $this->con->prepare("SELECT * FROM treatments WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $treatments = $stmt->get_result();
        $stmt->close();
        return $treatments;
    }

    public function getTreatment() {

    }

    public function consultHasTreatment($consult_id) {
        $stmt = $this->con->prepare("SELECT id FROM treatments WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function deleteConsultTreatments($consult_id) {
        $stmt = $this->con->prepare("DELETE FROM treatments WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function deleteTreatment($id, $patient_id, $consult_id, $current_consult_status) {
        $stmt = $this->con->prepare("DELETE FROM treatments WHERE id = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();

        if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS) {
            $medical_consult_status = $this->getMedicalConsultStatus($consult_id);
            if($medical_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING);
            } else if ($medical_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING);
            }
        }
    }

    public function deleteTreatmentForDiagnosis($diagnosis_id) {
        $stmt = $this->con->prepare("DELETE FROM treatments WHERE diagnosis_id = ?");
        $stmt->bind_param("s", $diagnosis_id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function createFollowup($patient_id, $current_consult_status, $consult_id, $is_needed, $is_type_custom, $type, $is_reason_custom, $reason, $notes) {
        $type = preg_replace("/\),\s\(/", "], [", $type);
        $reason = preg_replace("/\),\s\(/", "], [", $reason);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if($this->hasExistingFollowup($consult_id)) {
            return $this->updateExistingFollowup($consult_id, $is_needed, $is_type_custom, $type, $is_reason_custom, $reason, $notes);
        } else {
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO followups(id, consult_id, is_needed, is_type_custom, type, is_reason_custom, reason, notes) value(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $id, $consult_id, $is_needed, $is_type_custom, $type, $is_reason_custom, $reason, $notes);
            $result = $stmt->execute();
            $stmt->close();
            if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS);
            }
        }
    }

    public function updateExistingFollowup($consult_id, $is_needed, $is_type_custom, $type, $is_reason_custom, $reason, $notes) {
        $type = preg_replace("/\),\s\(/", "], [", $type);
        $reason = preg_replace("/\),\s\(/", "], [", $reason);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE followups SET is_needed = ?, is_type_custom = ?, type = ?, is_reason_custom = ?, reason = ?, notes = ? WHERE consult_id = ?");
        $stmt->bind_param("sssssss", $is_needed, $is_type_custom, $type, $is_reason_custom, $reason, $notes, $consult_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $consult_id;
        } else {
            return -2;
        }
    }

    public function hasExistingFollowup($consult_id) {
        $stmt = $this->con->prepare("SELECT id FROM followups WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getFollowup($consult_id) {
        $stmt = $this->con->prepare("SELECT * FROM followups WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $followup = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $followup;
    }

    public function consultHasFollowup($consult_id) {
        $stmt = $this->con->prepare("SELECT id FROM followups WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function deleteConsultFollowup($patient_id, $consult_id, $current_consult_status) {
        $stmt = $this->con->prepare("DELETE FROM followups WHERE consult_id = ?");
        $stmt->bind_param("s", $consult_id);
        $result = $stmt->execute();
        $stmt->close();

        if($current_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_IN_PROGRESS) {
            $medical_consult_status = $this->getMedicalConsultStatus($consult_id);
            if ($medical_consult_status == CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING) {
                $this->updateConsultStatus($patient_id, $consult_id, CONSULT_STATUS_READY_FOR_MEDICAL_CONSULT_PENDING);
            }
        }
    }


    //HISTORY STUFF BELOW
    public function createNewHistoryAllergy($patient_id, $allergy_id, $name, $notes, $date) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if ($this->hasExistingHistoryAllergy($allergy_id)) {
            return $this->updateHistoryAllergy($allergy_id, $name, $notes, $date);
        } else {
            if($name) {
                $stmt = $this->con->prepare("DELETE FROM history_allergies WHERE patient_id = ? AND name IS NULL");
                $stmt->bind_param("s", $patient_id);
                $result = $stmt->execute();
                $stmt->close();
            }
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO history_allergies(id, patient_id, name, notes, datetime_created, datetime_last_updated) values(?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $id, $patient_id, $name, $notes, $date, $date);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return $patient_id;
            } else {
                return -1;
            }
        }
    }

    public function updateHistoryAllergy($allergy_id, $name, $notes, $date) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE history_allergies SET name = ?, notes = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("ssss", $name, $notes, $date, $allergy_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $allergy_id;
        } else {
            return -2;
        }
    }

    public function hasExistingHistoryAllergies($patient_id) {
        $stmt = $this->con->prepare("SELECT id FROM history_allergies WHERE patient_id = ?");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function hasExistingHistoryAllergy($id) {
        $stmt = $this->con->prepare("SELECT id FROM history_allergies WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getHistoryAllergies($patient_id) {
        $stmt = $this->con->prepare("SELECT * FROM history_allergies WHERE patient_id = ? ORDER BY datetime_created DESC");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $allergies = $stmt->get_result();
        $stmt->close();
        return $allergies;
    }

    public function getHistoryAllergy($id) {
        $stmt = $this->con->prepare("SELECT * FROM history_allergies WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $allergy = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $allergy;
    }

    public function deleteHistoryAllergy($id) {
        $stmt = $this->con->prepare("DELETE FROM history_allergies WHERE id = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
    }

      public function createNewHistoryIllness($patient_id, $illness_id, $is_chronic, $type, $other, $start_date, $end_date, $notes, $datetime) {
        $other = preg_replace("/\),\s\(/", "], [", $other);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if ($this->hasExistingHistoryIllness($illness_id)) {
            return $this->updateHistoryIllness($illness_id, $is_chronic, $type, $other, $start_date, $end_date, $notes, $datetime);
        } else {
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO diagnoses_conditions_illnesses(id, patient_id, is_chronic, type, other, start_date, end_date, notes, datetime_created, datetime_last_updated) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $id, $patient_id, $is_chronic, $type, $other, $start_date, $end_date, $notes, $datetime, $datetime);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return $patient_id;
            } else {
                return -1;
            }
        }
    }

    public function updateHistoryIllness($illness_id, $is_chronic, $type, $other, $start_date, $end_date, $notes, $datetime) {
        $other = preg_replace("/\),\s\(/", "], [", $other);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE diagnoses_conditions_illnesses SET is_chronic = ?, type = ?, other = ?, start_date = ?, end_date = ?, notes = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("ssssssss", $is_chronic, $type, $other, $start_date, $end_date, $notes, $datetime, $illness_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $illness_id;
        } else {
            return -2;
        }
    }

    public function hasExistingHistoryIllness($id) {
        $stmt = $this->con->prepare("SELECT id FROM diagnoses_conditions_illnesses WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getHistoryIllnesses($patient_id) {
        $stmt = $this->con->prepare("SELECT * FROM diagnoses_conditions_illnesses WHERE patient_id = ? ORDER BY is_chronic DESC, datetime_created DESC");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $consults = $stmt->get_result();
        $stmt->close();
        return $consults;
    }

    public function getSpecificHistoryIllnesses($patient_id, $is_chronic) {

    }

    public function getHistoryIllness($id) {
        $stmt = $this->con->prepare("SELECT * FROM diagnoses_conditions_illnesses WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $consult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $consult;
    }

    public function deleteHistoryIllness($id) {
        $stmt = $this->con->prepare("DELETE FROM diagnoses_conditions_illnesses WHERE id = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function createNewHistorySurgery($patient_id, $surgery_id, $is_name_custom, $name, $date, $notes, $datetime) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if ($this->hasExistingHistorySurgery($surgery_id)) {
            return $this->updateHistorySurgery($surgery_id, $is_name_custom, $name, $date, $notes, $datetime);
        } else {
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO history_surgeries(id, patient_id, is_name_custom, name, date, notes, datetime_created, datetime_last_updated) values(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $id, $patient_id, $is_name_custom, $name, $date, $notes, $datetime, $datetime);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return $patient_id;
            } else {
                return -1;
            }
        }
    }

    public function updateHistorySurgery($surgery_id, $is_name_custom, $name, $date, $notes, $datetime) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE history_surgeries SET is_name_custom = ?, name = ?, date = ?, notes = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("ssssss", $is_name_custom, $name, $date, $notes, $datetime, $surgery_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $surgery_id;
        } else {
            return -2;
        }
    }

    public function hasExistingHistorySurgery($id) {
        $stmt = $this->con->prepare("SELECT id FROM history_surgeries WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getHistorySurgeries($patient_id) {
        $stmt = $this->con->prepare("SELECT * FROM history_surgeries WHERE patient_id = ? ORDER BY date DESC");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $consults = $stmt->get_result();
        $stmt->close();
        return $consults;
    }

    public function getHistorySurgery($id) {
        $stmt = $this->con->prepare("SELECT * FROM history_surgeries WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $consult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $consult;
    }

    public function deleteHistorySurgery($id) {
        $stmt = $this->con->prepare("DELETE FROM history_surgeries WHERE id = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
    }

    public function createNewHistoryMedication($patient_id, $consult_id, $medication_id, $name, $start_date, $end_date, $source, $notes, $datetime) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        if ($this->hasExistingHistoryMedication($medication_id)) {
            return $this->updateHistoryMedication($medication_id, $name, $start_date, $end_date, $source, $notes, $datetime);
        } else {
            $id = $this->generateUUID();
            $stmt = $this->con->prepare("INSERT INTO history_medications(id, patient_id, consult_id, name, start_date, end_date, source, notes, datetime_created, datetime_last_updated) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $id, $patient_id, $consult_id, $name, $start_date, $end_date, $source, $notes, $datetime, $datetime);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return $patient_id;
            } else {
                return -1;
            }
        }
    }

    public function updateHistoryMedication($medication_id, $name, $start_date, $end_date, $source, $notes, $datetime_last_updated) {
        $name = preg_replace("/\),\s\(/", "], [", $name);
        $notes = preg_replace("/\),\s\(/", "], [", $notes);

        $stmt = $this->con->prepare("UPDATE history_medications SET name = ?, start_date = ?, end_date = ?, source = ?, notes = ?, datetime_last_updated = ? WHERE id = ?");
        $stmt->bind_param("sssssss", $name, $start_date, $end_date, $is_self_reported, $notes, $datetime_last_updated, $medication_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $medication_id;
        } else {
            return -2;
        }
    }

    public function hasExistingHistoryMedication($id) {
        $stmt = $this->con->prepare("SELECT id FROM history_medications WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getHistoryMedications($patient_id) {
        $stmt = $this->con->prepare("SELECT * FROM history_medications WHERE patient_id = ? ORDER BY end_date IS NULL DESC, end_date DESC");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $consults = $stmt->get_result();
        $stmt->close();
        return $consults;
    }

    public function getHistoryMedication($id) {
        $stmt = $this->con->prepare("SELECT * FROM history_medications WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $consult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $consult;
    }

    public function deleteHistoryMedication($id) {
        $stmt = $this->con->prepare("DELETE FROM history_medications WHERE id = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
    }



}
