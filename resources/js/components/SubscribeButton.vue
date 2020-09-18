<template>
  <button :class="classes" @click="subscribe">Subscribe</button>
</template>

<script>
  export default {

    props: ['active'],

    computed: {
      classes(){
        return ['btn', this.active ? 'btn-primary' : 'btn-secondary']
      }
    },

    methods: {
      subscribe() {

        axios[this.active ? 'post' : 'delete'](location.pathname + '/subscriptions');

        this.active = ! this.active;
      }
    }
  }
</script>