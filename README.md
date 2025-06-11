# Polywarp

Keep your translations in sync between your laravel backend and frontend.

Fully typed.

## Installation

```sh
composer require itiden/laravel-polywarp
```

then install the vite run plugin (or import it from `./vendor/itiden/laravel-polywarp/vite-plugin/vite-plugin-polywarp`):

```sh
npm install --save-dev vite-plugin-polywarp
```

And then finally specify the plugin in your vite config:

```ts
import { polywarp } from "vite-plugin-run";

export default defineConfig({
  plugins: [
    // ...
    polywarp(),
  ],
});
```
