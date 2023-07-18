<?php

function getUtogiAPIURL()
{
    return API_URL_LIVE;
}

function getImageUrl()
{
    return UTOGI_IMAGE_URL_LIVE;
}

function query(string $query, array $variables = [], ?string $token = null): array
{

    $json = json_encode(['query' => $query, 'variables' => $variables]);

    return httpClient($token, $json);
}

function mutation(string $mutation, array $variables = [], ?string $token = null): array
{
    $json = json_encode(['query' => $mutation, 'variables' => $variables]);

    return httpClient($token, $json);
}

function httpClient(?string $token, $json): array
{
    $headers = ['Content-Type: application/json', 'User-Agent: utogi/wp'];
    if (null !== $token) {
        $headers[] = "Authorization: $token";
    }

    $chObj = curl_init();
    curl_setopt($chObj, CURLOPT_URL, getUtogiAPIURL());
    curl_setopt($chObj, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chObj, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($chObj, CURLOPT_POSTFIELDS, $json);
    curl_setopt(
        $chObj,
        CURLOPT_HTTPHEADER,
        array(
            'User-Agent: utogi/wp',
            'Content-Type: application/json;charset=utf-8',
            'Accept: application/json',
            'Authorization: ' . $token
        )
    );

    $response = curl_exec($chObj);
    curl_close($chObj);

    return json_decode($response, true);
}