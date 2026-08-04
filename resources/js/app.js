import Alpine from 'alpinejs';
import editor from './editor';
import { initRemoteForms } from './remote-forms';

window.Alpine = Alpine;

Alpine.data('editor', editor);

initRemoteForms();

Alpine.start();
