# Getting Started with Laravel Sail
⚠️ **Notice: This Documentation is in Development**  
This is actively being developed and may contain incomplete or changing instructions.

Laravel Sail is a lightweight command-line interface for running Laravel applications in a Docker environment.


### Quick Setup

-   *Ensure Docker is installed and running.*
-   *Ensure PHP and Composer are installed.*

1. **Environment setup**
    ```bash
    cp .env.example .env
    composer install --ignore-platform-reqs
    php artisan key:generate
    ```

2. **Install Laravel Sail**
    ```bash
    php artisan sail:install

    #┌── Which services would you like to install? ──┐
    #│   redis                                       │
    #│   mailpit                                     │
    #└───────────────────────────────────────────────┘
    ```
3.  **Access Sail Container**
    ```bash
    # Start the Docker containers in the background
    sail up -d

    # Access the Sail container's shell
    sail shell
    ```

    *If you haven't set up the sail alias, use*

    ```bash
    ./vendor/bin/sail up -d

    ./vendor/bin/sail shell
    ```

4. **Generate Key and Set Up Database**

    ```bash
    php artisan key:generate

    # Create SQLite DB
    touch database/database.sqlite

    php artisan migrate
    php artisan db:seed
    ```

5.**Install npm and Run in Development**
    ```bash
    npm install
    npm run development
    ```

6. **Configure your .env file with required API keys:**
    ```env
    # OpenAI API Key (required for AI features)
    OPENAI_API_KEY=your-openai-api-key

    # Pinecone API Key (required for vector search)
    PINECONE_API_KEY=your-pinecone-api-key
    PINECONE_ENVIRONMENT=your-pinecone-environment

    # Local AI API (optional, for local AI models)
    LOCAL_AI_URL=your-locla-ai-url
    LOCAL_AI_MODELS=your-ai-model
    ```

## Required API Keys

- **OpenAI API Key**: Get from [platform.openai.com](https://platform.openai.com/api-keys)
- **Pinecone API Key**: Get from [pinecone.io](https://www.pinecone.io/)
- **Locla AI**: Refer to [LM Studio](https://lmstudio.ai/docs/app/api-changelog) or [Ollama](https://github.com/ollama/ollama/blob/main/docs/api.md) documentation for setup.

Add these to your `.env` file to enable AI-powered features and vector search capabilities.
