const fs = require("fs").promises;
const del = require("del");
const zipdir = require("zip-dir");
const path = require("path");

async function copyDir(src, dest) {
  await fs.mkdir(dest, { recursive: true });
  let entries = await fs.readdir(src, { withFileTypes: true });
  let ignore = [
    "node_modules",
    "dist",
    "src",
    ".git",
    ".github",
    ".gitattributes",
    ".gitignore",
    ".vscode",
    "composer.json",
    "composer.lock",
    "package.json",
    "package-lock.json",
  ];

  for (let entry of entries) {
    if (ignore.indexOf(entry.name) != -1) {
      continue;
    }
    let srcPath = path.join(src, entry.name);
    let destPath = path.join(dest, entry.name);

    entry.isDirectory()
      ? await copyDir(srcPath, destPath)
      : await fs.copyFile(srcPath, destPath);
  }
}

(async () => {
  try {
    await del(["./dist"]);
    console.log("dist is deleted!");

    await copyDir("./", "./dist/sweet-portofolio/sweet-portofolio");

    zipdir(
      "./dist/sweet-portofolio",
      { saveTo: "./dist/sweet-portofolio.zip" },
      (err, buffer) => {
        if (err) {
          console.error(err);
        } else {
          console.log("Zip file created");
        }
      }
    );
  } catch (error) {
    console.error("Error:", error);
  }
})();
