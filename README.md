# Polywarp

Keep your translations in sync between your laravel backend and frontend.

Fully typed.

## Installation

```sh
composer require itiden/laravel-polywarp
```

And add the vite plugin in your vite config:

```ts
import { polywarp } from "./vendor/itiden/laravel-polywarp/vite-plugin/vite-plugin-polywarp";

export default defineConfig({
  plugins: [
    // ...
    polywarp(),
  ],
});
```
