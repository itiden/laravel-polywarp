import { exec } from "node:child_process";
import { minimatch } from "minimatch";
import osPath from "node:path";
import { promisify } from "util";
import { HmrContext, Plugin } from "vite";

const execAsync = promisify(exec);

interface PolywarpOptions {
  patterns?: string[];
}

export const polywarp = ({
  patterns = ["resources/js/**/*.ts", "lang/**/*.json", "lang/**/*.php"],
}: PolywarpOptions = {}): Plugin => {
  patterns = patterns.map((pattern) => pattern.replace("\\", "/"));

  const runCommand = async () => {
    await execAsync(`php artisan polywarp:generate`);
  };

  return {
    name: "@laravel/vite-plugin-polywarp",
    enforce: "pre",
    buildStart() {
      this.info('Running "php artisan polywarp:generate"');
      return runCommand();
    },
    async handleHotUpdate({ file, server }) {
      if (shouldRun(patterns, { file, server })) {
        await runCommand();
      }
    },
  };
};

const shouldRun = (
  patterns: string[],
  opts: Pick<HmrContext, "file" | "server">
): boolean => {
  const file = opts.file.replaceAll("\\", "/");

  if (
    file ===
    osPath.resolve(opts.server.config.root, "resources/js/translations.ts")
  )
    return false;

  return patterns.some((pattern) => {
    pattern = osPath
      .resolve(opts.server.config.root, pattern)
      .replaceAll("\\", "/");

    return minimatch(file, pattern);
  });
};
