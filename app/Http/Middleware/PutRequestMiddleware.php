<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * TODO: Implemented with assistance from AI (chatbot) and online resources.
 * Purpose: Enable multipart/form-data handling in PUT requests.
 */
class PutRequestMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->isMethod('PUT') && $request->headers->has('Content-Type') && str_contains($request->headers->get('Content-Type'), 'multipart/form-data')) {
            $body = $request->getContent();
            preg_match('/boundary=(.*)$/', $request->headers->get('Content-Type'), $matches);
            $boundary = '--' . ($matches[1] ?? '');

            $parts = array_slice(explode($boundary, $body), 1, -1);
            $data = [];
            $files = [];

            foreach ($parts as $part) {
                if (preg_match('/name="([^"]+)"; filename="([^"]+)"/', $part, $fileMatch)) {
                    $name     = $fileMatch[1];
                    $filename = $fileMatch[2];

                    if (preg_match('/Content-Type: ([^\r\n]+)/', $part, $typeMatch)) {
                        $contentType = trim($typeMatch[1]);
                    }

                    $fileBody = ltrim(explode("\r\n\r\n", $part)[1], "\r\n");
                    $fileBody = rtrim($fileBody, "\r\n--");
                    $tmpPath  = tempnam(sys_get_temp_dir(), 'upload_');
                    file_put_contents($tmpPath, $fileBody);

                    $uploadedFile = new UploadedFile(
                        $tmpPath,
                        $filename,
                        $contentType ?? null,
                        UPLOAD_ERR_OK,
                        true
                    );

                    $files[$name] = $uploadedFile;
                }

                elseif (preg_match('/name="([^"]+)"/', $part, $nameMatch)) {
                    $name  = $nameMatch[1];
                    $value = ltrim(explode("\r\n\r\n", $part)[1], "\r\n");
                    $data[$name] = rtrim($value, "\r\n--");
                }
            }


            $data['_method'] = 'PUT';
            $request->merge($data);

            foreach ($files as $key => $file) {
                $request->files->set($key, $file);
            }

            $request->setMethod('POST');
        }

        return $next($request);
    }
}
