<template>
    <div class="alert alert-flash" :class="'alert-'+level" role="alert" v-show="show" v-text="body"></div>
</template>

<script>
    export default {
      props: [
          'message'
      ],
      data(){
        return {
          level: 'success',
          body: this.message,
          show: false
        }
      },
      mounted() {
          console.log('Component mounted.')
      },
      created() {
        if(this.message){
          this.flash()
        }

        window.events.$on('flash', data => this.flash(data));
      },

      methods: {
        flash(data) {
          if(data){
            this.level = data.level;
            this.body = data.message;
          }
          this.show = true;

          this.hide();
        },

        hide() {
          setTimeout(() => {
            this.show = false;
          }, 3000);
        }
      }
    }
</script>

<style>
  .alert-flash {
    position: fixed;
    bottom: 25px;
    right: 25px;
  }
</style>