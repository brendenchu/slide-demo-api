import typescript from '@typescript-eslint/eslint-plugin'
import typescriptParser from '@typescript-eslint/parser'
import vue from 'eslint-plugin-vue'
import vueParser from 'vue-eslint-parser'
import prettier from 'eslint-config-prettier'

export default [
  {
    ignores: [
      '/.phpunit.cache',
      '/node_modules',
      '/public/build',
      '/public/hot',
      '/public/storage',
      '/storage/*.key',
      '/storage/debugbar',
      '/vendor',
      '.env',
      '.env.backup',
      '.env.production',
      '.phpunit.result.cache',
      'Homestead.json',
      'Homestead.yaml',
      'auth.json',
      'npm-debug.log',
      'yarn-error.log',
      '/.fleet',
      '/.idea',
      '/.vscode',
      'package-lock.json',
      'composer.lock',
    ],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: typescriptParser,
        ecmaVersion: 2021,
        sourceType: 'module',
      },
    },
    plugins: {
      '@typescript-eslint': typescript,
      vue,
    },
    rules: {
      'vue/prop-name-casing': 'off',
      'no-undef': 'off',
    },
  },
  prettier,
]
