module.exports = {
  extends: ["@commitlint/config-conventional"],
  rules: {
    "type-enum": [2, "always", ["feat", "fix", "chore", "refactor", "perf", "test", "docs", "ci", "build", "style","release"]],
    "subject-case": [2, "never", ["sentence-case", "start-case"]],
  },
};
