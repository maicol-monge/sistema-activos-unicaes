<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FacturaActivoAiService
{
    /**
     * Envía la factura a un servicio de IA externo y devuelve
     * un arreglo con campos sugeridos para el activo.
     *
     * Estructura esperada de retorno (keys opcionales):
     *  - nombre
     *  - tipo (FIJO|INTANGIBLE)
     *  - marca
     *  - serial
     *  - descripcion
     *  - fecha_adquisicion (YYYY-MM-DD)
     *  - valor_compra (float)
     */
    public function extraerDatos(UploadedFile $file): array
    {
        // Configuración para Google Gemini vía REST API
        // Usamos por defecto el modelo recomendado gemini-1.5-flash-latest en la versión v1
        $endpoint = config('services.facturas_ai.endpoint')
            ?: 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash-latest:generateContent';
        $apiKey = config('services.facturas_ai.api_key');

        if (!$endpoint || !$apiKey) {
            // Si no hay configuración de IA, no lanzamos error fatal
            // para no romper el flujo: simplemente devolvemos vacío.
            return [];
        }

        // Leemos el archivo y lo codificamos en base64 para enviarlo como inline_data
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $base64 = base64_encode(file_get_contents($file->getRealPath()));

        $prompt = <<<PROMPT
Eres un asistente que lee facturas o documentos de compra y extrae SOLO los datos necesarios para registrar un activo en el sistema.

Responde EXCLUSIVAMENTE en JSON plano, sin texto adicional, con la siguiente estructura (valores null si no se encuentran):
{
  "nombre": string|null,
  "tipo": "FIJO"|"INTANGIBLE"|null,
  "marca": string|null,
  "serial": string|null,
  "descripcion": string|null,
  "fecha_adquisicion": "YYYY-MM-DD"|null,
  "valor_compra": number|null
}

La fecha debe estar en formato ISO (YYYY-MM-DD) y el valor numérico en dólares (sin símbolo, usando punto decimal).
PROMPT;

        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                    [
                        'inline_data' => [
                            'mime_type' => $mime,
                            'data' => $base64,
                        ],
                    ],
                ],
            ]],
        ];

        $response = Http::timeout(60)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            // Gemini usa la API key como query param ?key=
            ->post($endpoint . '?key=' . urlencode($apiKey), $payload);

        if ($response->failed()) {
            $errorMessage = $response->json('error.message') ?? $response->body() ?? 'Error desconocido al llamar a Gemini.';
            throw new \RuntimeException('Gemini API error: ' . $errorMessage);
        }

        // El modelo debería responder con texto que es JSON; extraemos y unimos todas las partes de texto
        $parts = $response->json('candidates.0.content.parts', []);

        $rawText = '';
        if (is_array($parts)) {
            foreach ($parts as $part) {
                if (isset($part['text']) && is_string($part['text'])) {
                    $rawText .= $part['text'] . "\n";
                }
            }
        }

        $rawText = trim($rawText);

        if ($rawText === '') {
            return [];
        }

        // Algunos modelos devuelven ```json ... ```; limpiamos esos fence blocks si existen
        if (str_starts_with($rawText, '```')) {
            // Elimina líneas que empiezan con ``` y deja solo el contenido
            $lines = preg_split("/\r?\n/", $rawText);
            $filtered = [];
            foreach ($lines as $line) {
                if (preg_match('/^```/', trim($line))) {
                    continue;
                }
                $filtered[] = $line;
            }
            $rawText = trim(implode("\n", $filtered));
        }

        $decoded = json_decode($rawText, true);

        if (!is_array($decoded)) {
            // Si la respuesta no es JSON válido, no rompemos el flujo
            return [];
        }

        return [
            'nombre' => $decoded['nombre'] ?? null,
            'tipo' => isset($decoded['tipo']) && in_array($decoded['tipo'], ['FIJO', 'INTANGIBLE'])
                ? $decoded['tipo']
                : null,
            'marca' => $decoded['marca'] ?? null,
            'serial' => $decoded['serial'] ?? null,
            'descripcion' => $decoded['descripcion'] ?? null,
            'fecha_adquisicion' => $decoded['fecha_adquisicion'] ?? null,
            'valor_compra' => isset($decoded['valor_compra']) ? (float) $decoded['valor_compra'] : null,
        ];
    }
}
