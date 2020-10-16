<template>
  <div>
    <input id="trix" type="hidden" :value="value">

    <trix-editor ref="trix" input="trix" :placeholder="placeholder"></trix-editor>

  </div>
</template>
<script>
  import Trix from 'trix';

  export default {
    props: ['name','value', 'placeholder', 'shouldClear'],

    mounted() {

      // dato che mi è stato passato un v-model ed essendo questo un componenente custom devo emettere un evento quando l'input viene modificato.
      // essendo un trix editor ascolto il trix-change altrimenti sarebbe un change normale


      /*
      REFS DEVE ESSERE USATO QUANDO IL COMPONENTE è MONTATO

       */

      this.$refs.trix.addEventListener('trix-change', e => {
        this.$emit('input', e.target.innerHTML);
      });

      this.$watch('shouldClear', () => {
        this.$refs.trix.value = '';
      })
    },

  }
</script>