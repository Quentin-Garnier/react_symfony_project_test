import { registerReactControllerComponents } from '@symfony/ux-react';

console.log('Hello Webpack Encore! Edit me in assets/app.js');

registerReactControllerComponents(require.context('./react/controllers', true, /\.(j|t)sx?$/));