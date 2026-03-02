<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ReportesAiService
{
    /**
     * Envía una pregunta sobre reportes/activos al modelo Gemini junto con
     * datos agregados del sistema y devuelve una respuesta en texto plano.
     */
    public function responderConsulta(string $pregunta, array $contexto = []): string
    {
        $endpoint = config('services.facturas_ai.endpoint')
            ?: 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash-latest:generateContent';
        $apiKey = config('services.facturas_ai.api_key');

        if (!$endpoint || !$apiKey) {
            return '';
        }

        $contextJson = json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $prompt = <<<PROMPT
    Eres un asistente de análisis para el sistema de gestión de activos de la Universidad Católica de El Salvador (UNICAES).

    Recibirás:
    1) Una pregunta en español de un usuario con rol ADMIN o DECANO.
     2) Un objeto JSON con datos agregados del sistema, que puede incluir claves como:
         - activosPorCategoria, activosPorEstado, activosPorCondicion
         - reportesPorEstado, reportesPorMes
         - bajasPorEstado, bajasPorCategoriaAnio (bajas agrupadas por categoría y año)
         - asignacionesPorEstado, asignacionesPorUsuario (resumen por persona), asignacionesPorUsuarioAnio (por persona y año)
         - valorPorCategoria, histogramaValorActivos, scatterValorVsReportes

        Tu tarea:
        - Responde SIEMPRE en español.
        - Sé breve y claro: máximo 4-5 frases o viñetas.
        - Usa ÚNICAMENTE los datos numéricos presentes en el JSON de contexto; no inventes valores.
        - SIEMPRE que la pregunta se pueda contestar aproximando con esos agregados (por ejemplo, por año, por categoría o por usuario), RESPONDE usando esos datos.
        - Si el usuario menciona un rango de años (ej. 2011-2020), filtra mentalmente los datos de bajasPorCategoriaAnio o similares a ese rango y explica qué muestran, aunque el rango en la base real sea más corto.
        - Si realmente no hay NINGÚN dato relacionado (por ejemplo, no existe el usuario o categoría mencionada), dilo claramente y sugiere qué reporte o filtro usar.
        - No devuelvas JSON ni código, solo texto plano en español.

        Ejemplos de preguntas que SÍ debes intentar contestar con los datos:
        - "¿Qué categoría tuvo más bajas entre 2015 y 2020?" → usar bajasPorCategoriaAnio.
        - "Dame un análisis de los activos asignados a Marta López" → usar asignacionesPorUsuario filtrando por ese nombre.
        - "¿Cuál es el top 10 de usuarios con más activos asignados en 2026?" → usar asignacionesPorUsuarioAnio filtrando por ese año.
        - "¿En qué estado se encuentran la mayoría de los activos?" → usar activosPorEstado.
        - "¿Cuáles son las categorías con mayor valor total de activos?" → usar valorPorCategoria.
        - "¿En qué meses se han concentrado más reportes en el último año?" → usar reportesPorMes.
        - "¿Qué tan concentrado está el valor de los activos (pocos muy caros vs muchos baratos)?" → usar histogramaValorActivos.

        Ejemplo de estilo de respuesta cuando falte desglose temporal pero existan totales agregados por usuario:
        - Si preguntan: "¿Cuál es el top 10 de usuarios con más activos asignados en el año actual?" y SOLO tienes asignacionesPorUsuario con totales históricos, responde así:
            1) Explica primero que no puedes separar por año porque los datos son totales históricos.
            2) Luego muestra el top 10 de usuarios con más asignaciones históricas, indicando claramente que es histórico y no solo del año actual.
    PROMPT;

        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                    ['text' => "PREGUNTA DEL USUARIO:\n" . $pregunta],
                    ['text' => "DATOS AGREGADOS (JSON):\n" . $contextJson],
                ],
            ]],
        ];

        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint . '?key=' . urlencode($apiKey), $payload);

        if ($response->failed()) {
            return '';
        }

        $parts = $response->json('candidates.0.content.parts', []);

        $rawText = '';
        if (is_array($parts)) {
            foreach ($parts as $part) {
                if (isset($part['text']) && is_string($part['text'])) {
                    $rawText .= $part['text'] . "\n";
                }
            }
        }

        return trim($rawText);
    }
}
