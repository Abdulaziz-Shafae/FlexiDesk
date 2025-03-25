import eslint from 'eslint';

const { defineConfig } = eslint;

export default defineConfig([
  {
    files: ['src/**/*.{js,jsx}'], // Lint files in src folder
    parser: '@babel/eslint-parser',
    parserOptions: {
      ecmaVersion: 2020,
      sourceType: 'module',
      ecmaFeatures: {
        jsx: true,
      },
    },
    plugins: ['react', 'react-hooks'],
    rules: {
      'react/react-in-jsx-scope': 'off', // React 17 and later do not require React in scope for JSX
      'react/jsx-filename-extension': [1, { extensions: ['.jsx', '.js'] }],
    },
  },
]);
