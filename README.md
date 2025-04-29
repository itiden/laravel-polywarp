# Transfinder

Keep your translations in sync between your laravel backend and frontend.

Fully typed.

## Installation

```sh
composer require itiden/transfinder
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
      name: "transfinder",
      command: ["php", "artisan", "transfinder:generate"],
      pattern: ["resources/lang/**/*.php", "resources/js/**/*.ts"],
    }),
  ],
});
```
