<div class="flex h-screen text-sm">
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('markdown', () => ({
                // Single source of truth for markdown formatting rules
                // Markdown is always formatted on the client side for performance and consistency
                formatMarkdown(text) {
                    let processed = text;

                    // Process headlines first - we use strong tags instead of h1-h6 for visual simplicity
                    // This avoids complex heading hierarchies while maintaining semantic meaning
                    processed = processed.replace(/^(#{1,6})\s+(.+?)$/gm, '<strong>$2</strong>');

                    // Handle ordered lists - two-step process:
                    // 1. Convert each line to li with correct numbering
                    // 2. Wrap consecutive lis in ol tags
                    processed = processed.replace(/^\s*(\d+)\.\s+(.+)(?:\n|$)/gm, '<li value="$1">$2</li>');
                    processed = processed.replace(/(<li value="\d+">.+<\/li>\s*)+/gs, '<ol>$&</ol>');

                    // Handle unordered lists similarly
                    // Using negative lookbehind (?<!) to avoid double-wrapping ordered lists
                    processed = processed.replace(/^\s*[-*+]\s+(.+)(?:\n|$)/gm, '<li>$1</li>');
                    processed = processed.replace(/(?<!<\/ol>)(<li>.+<\/li>\s*)+/gs, '<ul>$&</ul>');

                    // Inline formatting last to avoid conflicts with block-level elements
                    processed = processed
                        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.+?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');

                    return processed;
                }
            }));
        });
    </script>

    <div class="flex-1 flex flex-col">
        <div class="flex-1 overflow-y-auto overscroll-contain flex flex-col-reverse">
            <div class="pt-4 pl-4 pr-4 flex flex-col">
                <!-- Message history -->
                @foreach($messages as $message)
                    <div class="mb-1 bg-white p-4 rounded-lg @if($message['role'] === 'assistant') !bg-gray-50 @endif">

                        <div class="text-xs text-gray-500 mb-1">
                            {{ $message['role'] === 'user' ? 'You' : 'Assistant' }}
                        </div>
                        <!-- Message history section -->
                        <!-- History messages use a different rendering approach than streaming -->
                        <div x-data="markdown" class="prose prose-sm -my-1 max-w-none">
                            <!-- Two-div approach for history:
                                 1. Hidden div stores original markdown (survives Livewire re-renders)
                                 2. Render div replaced with formatted content on init -->
                            <div style="display: none">{!! $message['content'] !!}</div>
                            <div x-init="
                                const content = $el.previousElementSibling.textContent;
                                if (content) {
                                    // Use outerHTML to avoid formatting being stripped by Livewire
                                    $el.outerHTML = formatMarkdown(content);
                                }
                            "></div>
                        </div>
                    </div>
                @endforeach

                <!-- Current exchange -->
                @if($currentQuestion)
                    <div class="mb-1 bg-white p-4 rounded-lg">
                        <div class="text-xs text-gray-500 mb-1">You</div>

                        <div class="prose prose-sm -my-1 max-w-none whitespace-pre-line">
                            {{ $currentQuestion }}
                        </div>
                    </div>
                    <div class="mb-1 bg-gray-50 p-4 rounded-lg">
                        <div class="text-xs text-gray-500 mb-1">Assistant</div>

                        <!-- Streaming message section -->
                        <!-- Key optimizations to prevent flicker and jumpiness: -->
                        <div x-data="markdown" class="prose prose-sm -my-1 max-w-none">
                            <!-- Hidden source element for Livewire streaming -->
                            <span wire:stream="currentAnswer" style="display: none" x-ref="source"></span>

                            <!-- Render target with optimized update strategy -->
                            <div x-init="
                                let lastText = '';  // Track last rendered content to prevent unnecessary updates
                                let animationFrame = null;  // Track pending animation frames

                                const observer = new MutationObserver(() => {
                                    const newText = $refs.source.textContent;

                                    // Only process changes when content actually updates
                                    if (newText !== lastText) {
                                        // Cancel any pending animation frame to avoid rapid re-renders
                                        if (animationFrame) {
                                            cancelAnimationFrame(animationFrame);
                                        }

                                        // Schedule render on next animation frame for smooth updates
                                        animationFrame = requestAnimationFrame(() => {
                                            const formatted = formatMarkdown(newText);
                                            // Only update DOM if formatted content changed
                                            if ($el.innerHTML !== formatted) {
                                                $el.innerHTML = formatted;
                                            }
                                            lastText = newText;
                                            animationFrame = null;
                                        });
                                    }
                                });

                                // Watch for all types of content changes
                                observer.observe($refs.source, {
                                    characterData: true,  // Text content changes
                                    childList: true,      // DOM structure changes
                                    subtree: true         // Changes in nested elements
                                });
                            "></div>
                        </div>

                    </div>
                @endif
            </div>
        </div>

        <!-- Input area -->
        <div class="border-t p-4 bg-white">
            <form wire:submit="sendMessage" class="flex gap-2">
                <input type="text"
                       wire:model="prompt"
                       class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:border-gray-500"
                       placeholder="Type your message..."
                       autocomplete="off"
                       autofocus>
                <button type="submit"
                        class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700">
                    Send
                </button>
            </form>
        </div>
    </div>

    <!-- Right column -->
    <div class="w-72 border-l flex flex-col overscroll-contain">

        <div class="overflow-y-auto overscroll-contain">
            <div class="p-4">
                Sidebar
            </div>
        </div>
    </div>
</div>
