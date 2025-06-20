<?php
if (!function_exists('res_data')) {
    function res_data($message = '', $data, $status = 200)
    {
        return response([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
