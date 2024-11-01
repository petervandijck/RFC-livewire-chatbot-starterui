## Livewire Chatbot UI Starterpack
Building a simple plain-text chatbot with Livewire is fairly easy, using wire:stream.
But user expectations are higher than that.
We think there is a need for a UI chatbot starter pack using Livewire that offers a few more features.

For some larger context, we think Laravel can quickly become a first-class citizen for building AI products, if we fill a few gaps. This might be one of them.

## Features
The codebase right now is just a quick start, it needs a lot of work.
- Implemented **streaming markdown rendering** with Livewire. This is tricky because:
  - We have to render incomplete markdown (it streams in incomplete chunks)
  - The UI gets jumpy during rendering
  - This is just a start with some implementation ideas.
- Future features
  - Stop conversation streaming
  - Better input box
  - Upload documents
  - Etc.

## RFC
This is a Request For Comments. You can download and run the code, but it's not a package, it's a conversation starter. 
- Pull requests with improvements welcome
- If you want to start a package from scratch, I'll link to it and support it

