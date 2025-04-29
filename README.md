# Polywarp

Keep your translations in sync between your laravel backend and frontend.

Fully typed.

## Installation

```sh
composer require itiden/polywarp
```

then install the vite run plugin

```sh
npm install --save-dev vite-plugin-run
```

And then finally specify the plugin in your vite config:

```ts
import { run } from "vite-plugin-run";

export default defineConfig({
  plugins: [
    // ...
    run({
      name: "polywarp",
      command: ["php", "artisan", "polywarp:generate"],
      pattern: ["resources/lang/**/*.php", "resources/js/**/*.ts"],
    }),
  ],
});
```
