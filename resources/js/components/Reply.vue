<template>
  <div :id="'reply-'+id" class="card" :class="isBest ? 'bg-success' : ''">
    <div class="card-header">
      <div class="d-flex align-items-center">
        <h5>
          <a :href="'/profiles/' + reply.owner.name"
             v-text="reply.owner.name">
          </a> said <span v-text="ago"></span>
        </h5>
        <favorite v-if="signedIn" :reply="reply"></favorite>
      </div>
    </div>

    <div class="card-body">
      <div v-if="editing">
        <form @submit="update">
          <div class="form-group">
            <wysiwyg v-model="body"></wysiwyg> <!-- il valore passato con v-model viene automaticamewnte bindato sul campo input del componente -->
          </div>

          <button class="btn btn-xs btn-primary">Update</button>
          <button type="button" class="btn btn-xs btn-link" @click="editing = false">Cancel</button>
        </form>
      </div>

      <div v-else v-html="body"></div>
    </div>

    <div class="card-footer d-flex" v-if="authorize('owns', reply) || (authorize('owns', reply.thread) && !isBest)">
      <div v-if="authorize('owns', reply)">
        <button class="btn btn-xs mr-1" @click="editing = true">Edit</button>
        <button class="btn btn-xs btn-danger mr-1" @click="destroy">Delete</button>
      </div>
      <button v-if="authorize('owns', reply.thread) && !isBest" class="btn btn-xs btn-primary ml-auto" @click="markBestReply">Best Reply?</button>
    </div>
  </div>
</template>

<script>
import Favorite from './Favorite.vue';
import moment from 'moment';

export default {
  props: ['reply'],

  components: { Favorite },

  data() {
    return {
      editing: false,
      id: this.reply.id,
      body: this.reply.body,
      members: [],
      isBest: this.reply.isBest,
    };
  },

  computed: {
    ago() {
      return moment(this.reply.created_at).fromNow() + '...';
    }
  },

  created() {

    window.events.$on('best-reply-selected', id => {
      this.isBest = (id === this.id);
    }) // ascolto un evento globale
  },

  methods: {
    update() {
      axios.patch(
          '/replies/' + this.id, {
            body: this.body
          })
          .catch(error => {
            flash(error.response.data, 'danger');
          });

      this.editing = false;

      flash('Updated!');
    },

    destroy() {
      axios.delete('/replies/' + this.id);

      this.$emit('deleted', this.id);
    },

    markBestReply() {
      axios.post('/replies/' + this.id + '/best').then(()=>{
        window.events.$emit('best-reply-selected', this.id) // emetto un evento globale
      });
    }
  }
}
</script>