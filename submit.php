<?php
    //google APIの読み込み
    error_reporting(0);
    require __DIR__."/vendor/autoload.php";
    $spreadsheetID = "1W_GssM6gVpiuBvBJa97InoloyIM0xB7RLNK3gRbFUoY";
function getClient()
{
    static $client = null;
    if($client === null){
    $key = __DIR__."/hisayalab-e49b42997889.json";
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
    $client->setAuthConfig($key);
    $client->setAccessType('offline');
    }

    return $client;
}

function appendRowToSpreadsheet($spreadsheetId, $range, $values) {
    $client = getClient();
    $service = new Google_Service_Sheets($client);

    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);

    $params = [
        'valueInputOption' => 'RAW'
    ];

    $insert = [
        "insertDataOption" => "INSERT_ROWS"
    ];

    $result = $service->spreadsheets_values->append(
        $spreadsheetId,
        $range,
        $body,
        $params,
        $insert
    );

    //printf("%d cells updated.", $result->getUpdates()->getUpdatedCells());
}

function getSpreadsheetDataWithHorizontalHeaderAsMap($spreadsheetId, $range) {
    $client = getClient();
    $service = new Google_Service_Sheets($client);
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();
    $map = [];

    if (empty($values)) {
        print "No data found.\n";
    } else {
        $headers = $values[0]; // 最初の行をヘッダーとして取得

        foreach($values as $rowIndex => $row) {
            if ($rowIndex === 0) {  // ヘッダー行をスキップ
                continue;
            }

            $entry = [];
            foreach($row as $colIndex => $cell) {
                $header = $headers[$colIndex];
                $entry[$header] = $cell;
            }
            $map[] = $entry;
        }
    }

    return $map;
}

function getSpreadsheetDataAsMap($spreadsheetId, $range) {
    $client = getClient();
    $service = new Google_Service_Sheets($client);
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();
    $map = [];
    if (count($values) == 0) {
        print "No data found.\n";
    } else {
        foreach($values as $row) {
            if(count($row) >= 2){//変更点：1列目と2列目が存在することを確認。
                $key = $row[0];    //変更点：1列目がキー。
                $value = $row[1];  //変更点：2列目が値。
                $map[$key] = $value;
            }else{
                $map = ["name" => "名無し",
                    "beforeSession" => "NaN",
                    "progress" => "0",
                    "sub" => "NoName"];
            }
        }
        return $map;
    }
}

function checkUpdateSubjectsList($spreadsheetID, $grading_info,$progress){
    $subjectMap = getSpreadsheetDataWithHorizontalHeaderAsMap($spreadsheetID,"subjectList!A:F");
    //被験者リスト内の今回の箇所にマーク
    $DataColumn = 2;
    foreach ($subjectMap as $s) {
        if($s["Name"] == $grading_info["subject"]){
            updateSpreadsheetDataFromArray($spreadsheetID,"subjectList!C".$DataColumn.":"."E".$DataColumn,[["Complete",$s["MoviePath"],$progress]]);
            break;
        }
        $DataColumn ++;
    }
    unset($s);
    unset($DataColumn);
    //完了していない最も上にあるリストにprogress等を変更
    $subjectMap = getSpreadsheetDataWithHorizontalHeaderAsMap($spreadsheetID,"subjectList!A:F");
    $DataColumn = 2;
    $DoUpdate = false;
    foreach ($subjectMap as $s) {
        if(!(strcmp($s["Status"] ,"Complete") == 0)){
            if($s["Progress"] == ""){
                $s["Progress"] = "1";
            }
            updateSpreadsheetDataFromArray($spreadsheetID,"GradingInfo!B2:B5",[[GetNowSession($grading_info)],[$s["Progress"]],[$s["Name"]],[$s["NumOfQuestions"]]]);
            updateSpreadsheetDataFromArray($spreadsheetID,"subjectList!C".$DataColumn,[["In progress"]]);
            $grading_info["subject"] = $s["Name"];
            $grading_info["progress"] = $s["Progress"];
            $grading_info["numOfQuestions"] = $s["NumOfQuestions"];
            $DoUpdate = true;
            break;
        }
        $DataColumn ++;
    }
    unset($s);
    unset($DataColumn);

    if(!$DoUpdate){
        //もしも完了していたら、名前をDoneに変える。
        $grading_info["subject"] = "Done";
        $grading_info["progress"] = "1";
        $grading_info["numOfQuestions"] = "1";
        updateSpreadsheetDataFromArray($spreadsheetID,"GradingInfo!B2:B5",[[GetNowSession($grading_info)],["1"],["Done"],["1"]]);
    }

    return $grading_info;
}
function updateSpreadsheetDataFromArray($spreadsheetId, $range, $array) {
    $client = getClient();
    $service = new Google_Service_Sheets($client);

    // Transform the associative array into two arrays: one for keys and one for values


    $data = [];
    $data[] = new Google_Service_Sheets_ValueRange([
        'range' => $range,
        'values' => $array // Values for the API are an indexed array of rows, each row being an indexed array of cells
    ]);

    // Additional parameters for the update
    $body = new Google_Service_Sheets_BatchUpdateValuesRequest([
        'valueInputOption' => "RAW",
        'data' => $data
    ]);

    // Send the update request to the Sheets API
    $result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

    //printf("Cells updated: %d\n", $result->getTotalUpdatedCells());
}
    // Check if request is a POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        try {
        $data = file_get_contents('php://input');
        $send_data = ['status' => 'OK'];
        // Do something with $data
        $decode_data = json_decode($data,true);

        //モードが認証モードだった場合
        if ($decode_data["mode"] == "authn") {
            //GradingInfoからデータを取得
            $grading_info = getSpreadsheetDataAsMap($spreadsheetID, "GradingInfo");

            //progressが最大値を越しているか、または、名前がDoneになっているかどうか
            if(intval($grading_info["progress"]) >= intval($grading_info["numOfQuestions"])|| strcmp($grading_info["subject"],"Done") == 0){
                $progress = intval($grading_info["progress"]);
                $grading_info = checkUpdateSubjectsList($spreadsheetID,$grading_info,$progress);
                $progress = $grading_info["progress"];
            }

            $grading_info_arr = ["name" => $grading_info["name"],
                                "beforeSession" => $grading_info["beforeSession"],
                                "progress" => $grading_info["progress"],
                                "subject" => $grading_info["subject"],
                                "numOfQuestions" => $grading_info["numOfQuestions"]];
            $send_data = array_merge($send_data, $grading_info_arr);

        }else if($decode_data["mode"] == "submit"){
            $grading_info = getSpreadsheetDataAsMap($spreadsheetID,"GradingInfo");
            $progress = intval($grading_info["progress"]);
            $client_progress = intval($decode_data["progress"]);
            $numOfQuestions = intval($grading_info["numOfQuestions"]);
            //クライアントの被験者とシート上に被験者が一致している
            if(strcmp($decode_data["subject"],$grading_info["subject"]) == 0){
                //クライアントの進捗とシート上の進捗が一致している
            if($progress == $client_progress){
                //progressと一緒であれば書き込む
                updateSpreadsheetDataFromArray($spreadsheetID,$grading_info["subject"]."!D".($progress+2).":G".($progress+2),[[$progress,$decode_data["label"],date("Y/m/d H:i:s") ,"GS-".$grading_info["name"]."-".$_SERVER["REMOTE_ADDR"]."-".userAgentInfo()]]);

                if($progress+1 > $numOfQuestions){
                    //TODO ここにmaxvalueを越した時の関数を書き込む
                    $grading_info = checkUpdateSubjectsList($spreadsheetID,$grading_info,$progress);
                    $progress = $grading_info["progress"];
                }else{
                    //次のステップに進む
                    $progress++;
                    updateSpreadsheetDataFromArray($spreadsheetID,"GradingInfo!B2:B3",[[GetNowSession($grading_info)],[strval($progress)]]);
                }

            }else{
                //記録されたprogressと一緒じゃないので戻す/スキップ
            }

            }else{
                //記録されたsubjectと一緒じゃないので被験者を更新する (この時の評定は書き込まない)

            }
            $grading_info_arr = ["name" => $grading_info["name"],
                "beforeSession" => $grading_info["beforeSession"],
                "progress" => $progress,
                "subject" => $grading_info["subject"],
                "numOfQuestions" => $grading_info["numOfQuestions"]];
            $send_data = array_merge($send_data, $grading_info_arr);
        }

        // Send response
        header('Content-Type: application/json');
        echo json_encode($send_data);
    } catch (Exception $e){
            echo json_encode(["status" => "NG","error" => $e["message"]]);
        }
    } else {
        // Not a POST request, handle accordingly
        header('HTTP/1.0 405 Method Not Allowed');
    }

function userAgentInfo() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // スマホかPCかを判定
    if (strpos($userAgent, 'iPhone') !== false) {
        $device = 'iPhone';
    } elseif (strpos($userAgent, 'iPad') !== false) {
        $device = 'iPad';
    } elseif (strpos($userAgent, 'Android') !== false) {
        if (strpos($userAgent, 'Mobile') !== false) {
            $device = 'Android Smartphone';
        } else {
            $device = 'Android Tablet';
        }
    } else {
        $device = 'PC';
    }
    // 使用していたブラウザの名称を判定
    if(preg_match('/MSIE/i', $userAgent))         $browser = "Internet Explorer";
    elseif(preg_match('/Firefox/i', $userAgent))  $browser = "Firefox";
    elseif(preg_match('/Chrome/i', $userAgent))   $browser = "Chrome";
    elseif(preg_match('/Safari/i', $userAgent))   $browser = "Safari";
    elseif(preg_match('/Opera/i', $userAgent))    $browser = "Opera";
    else $browser = 'Unknown';

    return $device."-".$browser;
}

    function GetNowSession($grading_info)
    {
        return date("Y/m/d H:i:s")."-GS-".$grading_info["name"]."-".$_SERVER["REMOTE_ADDR"]."-".userAgentInfo();
    }

    //TODO progressが0の時は処理を継続しない
    //TODO エラーを明確にする
    //TODO 表記揺れの改善
    //TODO コメントアウトの追加

    //ver 1.0　R06/03/28

    ?>