<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$GEMINI_API_KEY = 'AIzaSyDte2xLN9OKqDL4EeG5B0g0TPSufMOEy-o';
$GEMINI_URL = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . $GEMINI_API_KEY;

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$userMessage = trim($data['message'] ?? '');
$recentMood  = trim($data['recent_mood'] ?? '');

if (!$userMessage) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// Personalize with user's current mood context if available
$moodContext = '';
if ($recentMood) {
    $moodContext = "\n\n## Current User Context\nThe user's most recently logged mood is: {$recentMood}. Keep this in mind when responding.";
}

// System prompt — mental wellness context with sample Q&A dataset
$systemPrompt = "You are MindBloom AI, a compassionate mental wellness companion built into the MindBloom app.

## Your Role
- Provide emotional support, active listening, and gentle guidance
- Help users understand their moods and emotions
- Suggest healthy coping strategies
- Keep responses warm, concise (2-4 sentences), and supportive
- Never diagnose medical conditions or replace professional therapy
- If someone is in crisis, ALWAYS recommend calling 988 (Suicide & Crisis Lifeline) or 911

## Tone & Style
- Warm, empathetic, non-judgmental
- Use simple, conversational language
- Occasionally use gentle emojis (💚 🌿 💙 🌸)
- Validate feelings before offering advice
- Respond in the same language the user writes in (Filipino or English)

## Sample Responses (use as reference for tone)

User: 'I feel so sad today'
Response: 'I hear you, and it's okay to feel sad 💙. Your feelings are valid. Would you like to talk about what's been weighing on you, or would a simple breathing exercise help right now?'

User: 'I'm so stressed with work'
Response: 'Work stress can feel really overwhelming 🌿. Try taking 3 slow deep breaths right now — in for 4 counts, hold for 4, out for 4. What's the biggest thing stressing you out today?'

User: 'I feel anxious and can't sleep'
Response: 'Anxiety at night is really tough 💚. Try the 5-4-3-2-1 grounding technique: name 5 things you can see, 4 you can touch, 3 you can hear, 2 you can smell, 1 you can taste. This helps bring your mind back to the present.'

User: 'I want to hurt myself'
Response: 'I'm really concerned about you right now. Please reach out to the 988 Suicide & Crisis Lifeline by calling or texting 988 — they're available 24/7 and truly want to help. You don't have to face this alone. 💙'

User: 'I feel happy today!'
Response: 'That's wonderful to hear! 🌸 Savoring positive moments actually helps them last longer in your memory. What's been bringing you joy today?'

User: 'Paano ko malalampasan ang kalungkutan?'
Response: 'Ang kalungkutan ay natural na bahagi ng buhay 💙. Subukan mong ibahagi ang iyong nararamdaman sa isang taong pinagkakatiwalaan mo, o isulat ito sa iyong journal. Nandito ako para makinig sa iyo.'

User: 'I feel angry all the time'
Response: 'Feeling angry frequently can be exhausting 💚. Anger often signals that something important to you isn't being respected. Try a quick physical release — even a brisk 5-minute walk can help. What do you think is triggering this?'

User: 'I feel lonely'
Response: 'Loneliness is one of the hardest feelings to sit with 🌿. You reached out today, and that takes courage. Even small connections matter — is there one person you could send a simple message to right now?'

## What You Know About MindBloom
- Users can log their moods (happy, calm, sad, anxious, angry, neutral)
- They can write journal entries to process their feelings
- The app shows mood trends and patterns over time
- The color theme changes based on their mood using color psychology
- Crisis help resources are available in the app";


$payload = [
    'contents' => [
        [
            'role'  => 'user',
            'parts' => [['text' => $systemPrompt . $moodContext . "\n\nNow respond to this message from the user: " . $userMessage]]
        ]
    ],
    'generationConfig' => [
        'temperature'     => 0.8,
        'maxOutputTokens' => 300,
    ]
];

$ch = curl_init($GEMINI_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['error' => 'Connection error: ' . $curlErr]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode === 429) {
    // Rate limited — use local fallback responses
    $fallbacks = [
        "I'm here for you 💚 Our AI is taking a short break, but I'm still listening. How are you feeling right now?",
        "Thank you for reaching out 🌿 The AI is temporarily unavailable, but your feelings matter. Try journaling your thoughts while we reconnect.",
        "I hear you 💙 Our AI companion is resting for a moment. Please try again in a few minutes — I'll be ready to listen.",
    ];
    echo json_encode(['reply' => $fallbacks[array_rand($fallbacks)], 'fallback' => true]);
    exit;
}

if ($httpCode !== 200 || !isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode([
        'error'    => 'AI unavailable',
        'detail'   => $result['error']['message'] ?? 'Unknown',
        'httpCode' => $httpCode,
        'raw'      => $result
    ]);
    exit;
}

$reply = $result['candidates'][0]['content']['parts'][0]['text'];
echo json_encode(['reply' => $reply]);
?>
