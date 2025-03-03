// assets/controllers/hello_controller.js
import { Controller } from 'stimulus';

export default class extends Controller {
  connect() {
    this.element.textContent = 'Hello Stimulus!';
  }
}
