import '../css/app.css';
import { mount } from 'svelte';
import Example from '../components/Example.svelte';

// Mount Example component.
const exampleEl = document.getElementById('example-component');
if (exampleEl) {
  const config = JSON.parse(exampleEl.dataset.config || '{}');
  mount(Example, {
    target: exampleEl,
    props: config,
  });
}
