import { Application } from 'stimulus';
import { definitionsFromContext } from 'stimulus/webpack-helpers';

// Start the Stimulus application
const application = Application.start();

// Automatically load controllers from the 'assets/controllers' directory
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));
