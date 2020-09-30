<template>
  <input type="file" accept="image/*" @change="onChange">
</template>

<script>
  export default {
    methods: {
      onChange(e){

        if(!e.target.files.length) return;

        let file = e.target.files[0];

        let reader = new FileReader(); // Javascript api per leggere il contenuto di un file

        reader.readAsDataURL(file); // leggo il contenuto del file

        // Quando ho letto il file
        reader.onload = e => {

          let src = e.target.result;
          this.$emit('loaded', {file, src}); // emetto un evento sul padre passando l'oggetto file e il contenuto del file

        }
      },
    }
  }
</script>