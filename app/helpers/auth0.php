<?php
function get_access_token()
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://".env('AUTH0_DOMAIN')."/oauth/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=".env('AUTH0_CLIENT_ID')."&client_secret=".env('AUTH0_CLIENT_SECRET')."&audience=".env('AUTH0_AUDIENCE'),
        CURLOPT_HTTPHEADER => [
        "content-type: application/x-www-form-urlencoded"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    // return ("cURL Error #:" . $err);
    return "";
    } else {
    $response = json_decode($response);
        return $response->access_token;
    }
}

function update_user_meta($access_token,$url,$metadata)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PATCH",
        CURLOPT_POSTFIELDS => "{\"user_metadata\": $metadata}",
        CURLOPT_HTTPHEADER => [
        "authorization: Bearer $access_token",
        "content-type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    //   dd("cURL Error #:" . $err);
    } else {
    //   dd($response);
    }
}
?>
