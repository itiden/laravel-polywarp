import { minimatch } from "minimatch";
import { exec } from "node:child_process";
import osPath from "node:path";
import { promisify } from "util";
import { HmrContext, Plugin } from "vite";

type ConfigCommanResult = {
  content_paths: string[];
  output_path: string;
  translation_directories: string[];
};

const execAsync = promisify(exec);

const normalizePath = (path: string) => path.replaceAll("\\", "/");

export const polywarp = (): Plugin => {
  let content_paths = new Array<string>();
  let translation_paths = new Array<string>();
  let outfile = "";

  let paths = new Array<string>();

  const runCommand = async () => {
    await execAsync(`php artisan polywarp:generate`);
  };

  return {
    name: "@itiden/vite-plugin-polywarp",
    enforce: "pre",
    async config() {
      const { stderr, stdout } = await execAsync("php artisan polywarp:config");

      if (stderr) {
        throw new Error("Failed to execute polywarp:config command");
      }

      const config: ConfigCommanResult = JSON.parse(stdout);

      content_paths = config.content_paths.map(normalizePath);
      translation_paths = config.translation_directories.map(normalizePath);
      outfile = normalizePath(config.output_path);

      paths = [...content_paths, ...translation_paths];
    },
    async buildStart() {
      return runCommand().then(() =>
        this.environment.logger.info("generated polywarp files")
      );
    },
    async hotUpdate({ file, server }) {
      if (shouldRun(paths, { file, server }, outfile)) {
        await runCommand().then(() =>
          this.environment.logger.info("generated polywarp files")
        );
      }
    },
  };
};

const shouldRun = (
  patterns: string[],
  opts: Pick<HmrContext, "file" | "server">,
  outfile: string
): boolean => {
  const file = normalizePath(opts.file);

  // If the file is the output file, we don't want to run the command
  if (file === outfile) return false;

  return patterns.some((pattern) => {
    pattern = normalizePath(osPath.resolve(opts.server.config.root, pattern));

    return minimatch(file, pattern);
  });
};
