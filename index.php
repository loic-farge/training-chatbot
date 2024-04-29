<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Initialize and load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use LucianoTonet\GroqPHP\Groq;
$groq = new Groq($_ENV['GROQ_API_KEY']);

$information = file_get_contents('./information.txt');

// Initial message to set the context
$conversationHistory = [
    [
        'role' => 'system',
        'content' => 'The system is a person who will answer questions about me depending on a certain amount of data he possesses:
'.$information.'
Today we are the ' . date('Y-M-d') . '
At the beginning, the system will greet the user only with a good morning, I am Loic, what would you like to know about my professional experience? Then it will wait for questions from the user and answer them if the system has the answer. In case the system does not know the answer, it will answer: “Sorry, I don’t have this information, but feel free to connect on LinkedIn (https://www.linkedin.com/in/lo%C3%AFc-farge-b1396124/) and let’s have a coffee chat session together.”'
    ],
    [
        'role' => 'assistant',
        'content' => 'Good morning, I am Loic, what would you like to know about my professional experience?'
    ]
];

echo "Assistant: ".$conversationHistory[1]['content']."\n";

// Start conversation loop
while (true) {
    // Get user input
    echo "You: ";
    $userInput = trim(fgets(STDIN));

    // Check for exit condition
    if (strtolower($userInput) === 'bye') {
        echo "Chat ended. Goodbye!\n";
        break;
    }

    // Prepare the user message for the API
    $conversationHistory[] = [
        'role' => 'user',
        'content' => $userInput
    ];

    // API call to Groq for a response
    $chatCompletion = $groq->chat()->completions()->create([
        'temperature' => 0,
        'model'    => 'llama3-8b-8192',
        'messages' => $conversationHistory
    ]);

    $apiResponse = $chatCompletion['choices'][0]['message']['content'];

    // Display AI response and update history
    echo "Assistant: $apiResponse\n";
    $conversationHistory[] = [
        'role' => 'assistant',
        'content' => $apiResponse
    ];
}