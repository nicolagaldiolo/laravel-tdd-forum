<template>
  <div>
    <div class="d-flex align-items-center">
      <img :src="avatar" width="50" class="mr-2">
      <h1 class="m-0" v-text="user.name"></h1>
    </div>
    <div class="mt-2">
      <form v-if="canUpdate" method="post" enctype="multipart/form-data">
        <image-upload name="avatar" class="form-control-file" @loaded="onLoad"></image-upload>
      </form>
    </div>

  </div>
</template>

<script>
  import ImageUpload from "./ImageUpload";
  export default {
    components: {ImageUpload},
    props: [ 'user'],

    data(){
      return {
        'avatar': this.user.avatar_path
      }
    },

    computed: {
      canUpdate() {
        return this.authorize(user => user.id === this.user.id);
      }
    },

    methods: {
      onLoad(avatar){

        this.avatar = avatar.src // estraggo il base64 del file e lo assegno ad avatar
        this.persist(avatar.file);
      },

      persist(file){
        let data = new FormData(); // Dato che il form è multipart/form-data creo un FormData e gli aggiungo l'avatar

        data.append('avatar', file); // Non devo passare il contenuto del file ma devo passare l'oggetto file

        axios.post(`/api/users/${this.user.name}/avatar`, data)
            .then(()=> flash('Avatar Uploaded!'));
      }
    }
  }
</script>