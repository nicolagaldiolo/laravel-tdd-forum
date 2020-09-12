<template>
  <button :class="classes" @click="toggle">
    <i class="fa fa-eye" aria-hidden="true"></i>
    {{ count }}
  </button>
</template>

<script>
    export default {
      props: [ 'reply'],

      data() {
        return {
          count: this.reply.favoritesCount,
          active: this.reply.isFavorited
        }
      },

      computed : {
        classes() {
          return [
              'btn',
              'ml-auto',
              this.active ? 'btn-primary' : 'btn-secondary'
          ]
        }
      },

      methods: {
        toggle() {
          this.active ? this.destroy() : this.create()
        },

        destroy(){
          axios.delete('/replies/' + this.reply.id + '/favorites').then(res => {
            this.active = false;
            this.count--;
            flash('Removed from favorites');
          });
        },

        create(){
          axios.post('/replies/' + this.reply.id + '/favorites').then(res => {
            this.active = true;
            this.count++;
            flash('Added to favorites');
          });
        }
      }
    }
</script>