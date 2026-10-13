<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WordCounterService
{
    /**
     * Analyze text and return detailed metrics.
     */
    public function analyze(string $text): array
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return [
                'words' => 0,
                'characters_with_spaces' => 0,
                'characters_no_spaces' => 0,
                'sentences' => 0,
                'paragraphs' => 0,
                'reading_time' => '0 dtk',
                'speaking_time' => '0 dtk',
            ];
        }

        // Count words (supports unicode / multilingual letters & numbers)
        preg_match_all('/[\p{L}\p{N}]+/u', $trimmed, $wordMatches);
        $wordCount = count($wordMatches[0] ?? []);

        // Character counts
        $charWithSpaces = mb_strlen($text, 'UTF-8');
        $charNoSpaces = mb_strlen(preg_replace('/\s+/u', '', $text), 'UTF-8');

        // Sentences count (split by punctuation followed by space or end of string)
        $sentences = preg_split('/[.!?]+(?:\s+|$)/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = is_array($sentences) ? count($sentences) : 0;

        // Paragraphs count (split by single or multiple line breaks)
        $paragraphs = preg_split('/\R+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
        $paragraphCount = is_array($paragraphs) ? count($paragraphs) : 0;

        // Reading time (~200 words per minute)
        $readingSeconds = (int) ceil(($wordCount / 200) * 60);
        $readingTime = $readingSeconds < 60
            ? $readingSeconds . ' dtk'
            : round($readingSeconds / 60, 1) . ' mnt';

        // Speaking time (~130 words per minute)
        $speakingSeconds = (int) ceil(($wordCount / 130) * 60);
        $speakingTime = $speakingSeconds < 60
            ? $speakingSeconds . ' dtk'
            : round($speakingSeconds / 60, 1) . ' mnt';

        return [
            'words' => $wordCount,
            'characters_with_spaces' => $charWithSpaces,
            'characters_no_spaces' => $charNoSpaces,
            'sentences' => $sentenceCount,
            'paragraphs' => $paragraphCount,
            'reading_time' => $readingTime,
            'speaking_time' => $speakingTime,
        ];
    }

    /**
     * Convert text casing or perform text cleaning.
     */
    public function convertCase(string $text, string $mode): string
    {
        if (trim($text) === '') {
            return $text;
        }

        return match ($mode) {
            'upper' => mb_strtoupper($text, 'UTF-8'),
            'lower' => mb_strtolower($text, 'UTF-8'),
            'title' => mb_convert_case($text, MB_CASE_TITLE, 'UTF-8'),
            'sentence' => (function () use ($text) {
                $lower = mb_strtolower($text, 'UTF-8');
                return preg_replace_callback('/(^|[.!?]\s+)(\p{Ll})/u', function ($matches) {
                    return $matches[1] . mb_strtoupper($matches[2], 'UTF-8');
                }, $lower);
            })(),
            'camel' => (function () use ($text) {
                // Split by lines to preserve multiline structure while converting lines
                $lines = preg_split('/\R/u', $text);
                $converted = array_map(function ($line) {
                    $trimmed = trim($line);
                    return $trimmed !== '' ? Str::camel($trimmed) : $line;
                }, $lines);
                return implode("\n", $converted);
            })(),
            'snake' => (function () use ($text) {
                $lines = preg_split('/\R/u', $text);
                $converted = array_map(function ($line) {
                    $trimmed = trim($line);
                    return $trimmed !== '' ? Str::snake($trimmed) : $line;
                }, $lines);
                return implode("\n", $converted);
            })(),
            'kebab' => (function () use ($text) {
                $lines = preg_split('/\R/u', $text);
                $converted = array_map(function ($line) {
                    $trimmed = trim($line);
                    return $trimmed !== '' ? Str::kebab($trimmed) : $line;
                }, $lines);
                return implode("\n", $converted);
            })(),
            'clean_spaces' => preg_replace('/[ \t]+/u', ' ', $text),
            'clean_empty_lines' => preg_replace('/(\R){2,}/u', "\n", $text),
            default => $text,
        };
    }

    /**
     * Export text content to a downloadable .txt file.
     */
    public function exportTxt(string $text): array
    {
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'word_counter_' . $uniqueId . '.txt';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        Storage::disk('local')->put($relativeResultPath, $text);

        return [
            'result_path' => $relativeResultPath,
            'result_filename' => 'dokumen_teks_' . time() . '.txt',
            'size' => strlen($text),
        ];
    }
}
