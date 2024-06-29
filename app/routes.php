<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Smalot\PdfParser\Parser;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->post('/upload', function (Request $request, Response $response) {
        $uploadedFiles = $request->getUploadedFiles();
        $pdfFile = $uploadedFiles['pdf'];

        if ($pdfFile->getError() === UPLOAD_ERR_OK) {
            $uploadsDir = __DIR__ . '/../uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }
            $filename = $pdfFile->getClientFilename();
            $filePath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;
            $pdfFile->moveTo($filePath);

            $fileId = uniqid();

            // Save the file ID and path
            $fileData = [
                'id' => $fileId,
                'path' => $filePath,
                'word_count' => null,
            ];

            file_put_contents($uploadsDir . DIRECTORY_SEPARATOR . $fileId . '.json', json_encode($fileData));

            $response->getBody()->write(json_encode(['file_id' => $fileId]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Failed to upload file.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    });

    $app->post('/process/{file_id}', function (Request $request, Response $response, array $args) {
        try {
            $uploadsDir = __DIR__ . '/../uploads';
            $fileId = $args['file_id'];
            $fileDataPath = $uploadsDir . DIRECTORY_SEPARATOR . $fileId . '.json';

            if (!file_exists($fileDataPath)) {
                $response->getBody()->write(json_encode(['error' => 'File not found.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $fileData = json_decode(file_get_contents($fileDataPath), true);
            $filePath = $fileData['path'];

            if (!class_exists('Smalot\PdfParser\Parser')) {
                throw new Exception('Class "Smalot\PdfParser\Parser" not found');
            }

            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            $wordCount = str_word_count($text);

            $fileData['word_count'] = $wordCount;
            file_put_contents($fileDataPath, json_encode($fileData));

            $response->getBody()->write(json_encode(['file_id' => $fileId, 'word_count' => $wordCount]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => [
                    'type' => 'SERVER_ERROR',
                    'description' => $e->getMessage(),
                ]
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    $app->get('/result/{file_id}', function (Request $request, Response $response, array $args) {
        $uploadsDir = __DIR__ . '/../uploads';
        $fileId = $args['file_id'];
        $fileDataPath = $uploadsDir . DIRECTORY_SEPARATOR . $fileId . '.json';

        if (!file_exists($fileDataPath)) {
            $response->getBody()->write(json_encode(['error' => 'File not found.']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $fileData = json_decode(file_get_contents($fileDataPath), true);

        $response->getBody()->write(json_encode($fileData));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
