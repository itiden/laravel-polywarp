import { minimatch } from "minimatch";
import { exec } from "node:child_process";
import osPath, { resolve } from "node:path";
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
  let laravel_polywarp_config_path = "";

  let content_paths = new Array<string>();
  let translation_paths = new Array<string>();
  let outfile = "";

  // instead of having to spread or merge the content/translation paths we save them in a single array
  // this way we can just check if the file changed is in any of the content or translation paths
  // this is useful for the hotUpdate method which may be called quite often
  let paths = new Array<string>();

  const runCommand = async (
    cache?: "used-translations" | "available-translations"
  ) => {
    const command = "php artisan polywarp:generate";

    await execAsync(
      [command, cache ? `--use-cache-for=${cache}` : null]
        .filter(Boolean)
        .join(" ")
    );
  };

  return {
    name: "@itiden/vite-plugin-polywarp",
    enforce: "pre",
    async config(conf) {
      const { stderr, stdout } = await execAsync("php artisan polywarp:config");

      if (stderr) {
        throw new Error("Failed to execute polywarp:config command");
      }

      const config: ConfigCommanResult = JSON.parse(stdout);

      content_paths = config.content_paths.map(normalizePath);
      translation_paths = config.translation_directories.map(normalizePath);
      outfile = normalizePath(config.output_path);

      laravel_polywarp_config_path = resolve(
        conf.root ?? process.cwd(),
        "config/polywarp.php"
      );

      paths = [...content_paths, ...translation_paths];
    },
    async buildStart() {
      return runCommand();
    },
    async hotUpdate({ file, server }) {
      if (file === laravel_polywarp_config_path) {
        this.environment.logger.clearScreen("info");
        this.environment.logger.info(
          "config/polywarp.php config file changed, restarting server",
          {
            timestamp: true,
          }
        );
        server.restart();
        return;
      }

      if (shouldRun(content_paths, { file, server }, outfile)) {
        await runCommand("available-translations");
        return;
      }

      if (shouldRun(translation_paths, { file, server }, outfile)) {
        await runCommand("used-translations");
        return;
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
