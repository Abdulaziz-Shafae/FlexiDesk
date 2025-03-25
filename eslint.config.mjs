import { defineConfig } from 'eslint-define-config';

export default defineConfig({
  extends: [
    'eslint:recommended', // Applies basic recommended ESLint rules
    'plugin:react/recommended', // React-specific rules
    'airbnb', // Uses Airbnb style guide rules
    'prettier', // Applies Prettier rules to avoid formatting conflicts
  ],
  parser: '@babel/eslint-parser', // Needed to parse React code properly
  parserOptions: {
    ecmaVersion: 2020, // ECMAScript version for parsing
    sourceType: 'module', // Allows for ES module syntax
    ecmaFeatures: {
      jsx: true, // Allows JSX syntax
    },
  },
  plugins: ['react', 'react-hooks'], // Includes React and React Hooks plugins
  rules: {
    'react/react-in-jsx-scope': 'off', // React 17 and later don't need React in every JSX
    'react/jsx-filename-extension': [1, { extensions: ['.jsx', '.js'] }], // Allows .js and .jsx files for JSX code
  },
});
