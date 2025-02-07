<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    public const document_formats = [
        'application/octet-stream',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessing',
        'application/vnd.rar',
        'application/zip',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/rtf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/xml', 'text/xml',
    ];
    public const image_formats = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/vnd.microsoft.icon',
        'image/svg+xml',
        'image/tiff',
        'image/bmp',
        'application/octet-stream',
    ];
    public const video_formats = [
        'video/mp4',
        'video/x-flv',
        'video/x-matroska',
        'video/webm',
        'video/mpeg',
        'video/x-msvideo',
        'video/3gpp',
        'video/quicktime',
        'application/x-mpegURL',
        'video/MP2T',
        'video/x-ms-wmv',
        'application/octet-stream',
    ];

    public static function upload($data, $mediaFormat, $folder)
    {
        $path = [];
        $path['error'] = null;

        if ($mediaFormat == 'image') {
            if (!in_array($data->getClientMimeType(), self::image_formats)) {
                $path['error'] = 'Wrong file format, only png, jpeg, gif, ico, svg, and tiff accepted';
                return $path;
            }
            $paths = $folder . '/images';
        }
        if ($mediaFormat == 'video') {
            if (!in_array($data->getClientMimeType(), self::video_formats)) {
                $path['error'] = 'Wrong file format, only mp4, flv, mkv, webm, mpeg, 3gp, and avi accepted';
                return $path;
            }
            $paths = $folder . '/videos';
        }
        if ($mediaFormat == 'document') {
            if (!in_array($data->getClientMimeType(), self::document_formats)) {
                $path['error'] = 'Wrong file format, only pdf, doc, docx, rar, zip, odp, ods, odt, ppt, pptx, rtf, xls, xlsx and xml accepted';
                return $path;
            }
            $paths = $folder . '/documents';
        }
        if ($mediaFormat == 'imageAndVideo') {
            if ((!in_array($data->getClientMimeType(), self::image_formats)) && (!in_array($data->getClientMimeType(), self::video_formats))) {
                $path['error'] = 'Wrong file format';
                return $path;
            }
            if (in_array($data->getClientMimeType(), self::video_formats)) {
                $paths = $folder . '/videos/' . rand(9, 999999);
            }
            if (in_array($data->getClientMimeType(), self::image_formats)) {
                $paths = $folder . '/images/' . rand(9, 999999);
            }
        }
        if ($mediaFormat == 'all') {
            if ((!in_array($data->getClientMimeType(), self::document_formats)) && (!in_array($data->getClientMimeType(), self::image_formats)) && (!in_array($data->getClientMimeType(), self::video_formats))) {
                $path['error'] = 'Wrong file format';
                return $path;
            }
            if (in_array($data->getClientMimeType(), self::document_formats)) {
                $paths = $folder . '/documents/' . rand(9, 999999);
            }
            if (in_array($data->getClientMimeType(), self::video_formats)) {
                $paths = $folder . '/videos/' . rand(9, 999999);
            }
            if (in_array($data->getClientMimeType(), self::image_formats)) {
                $paths = $folder . '/images/' . rand(9, 999999);
            }
        }
        $path = self::uploadToS3($data, $paths);

        return $path;
    }
//---------------Functions to upload to s3--------------------------------
    private static function uploadToS3($data, $path)
    {
        $currentDateTime = Carbon::now();
        $file = $data;
        $extension = $file->getClientOriginalExtension();
        if (Storage::disk('s3')->put(
            $path . $currentDateTime . '.' . $extension, #$path
            file_get_contents($file), #$fileContent
            'public')) {
            $pathurl = $path . $currentDateTime . '.' . $extension;
            return $pathurl;
        }
    }
}
