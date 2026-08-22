{
  "extends": "eslint:recommended",
  "env": {
    "browser": true,
    "es2022": true,
    "node": true
  },
  "parserOptions": {
    "ecmaVersion": "latest",
    "sourceType": "module",
    "ecmaFeatures": {
      "jsx": true
    }
  },
  "ignorePatterns": ["public/build/**", "node_modules/**", "vendor/**"]
}
