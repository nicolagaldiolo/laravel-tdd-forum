<template>
    <div :id="'reply-' + id" class="card">

      <div class="card-header">
        <div class="d-flex align-items-center">
          <h5>
            <a :href="'/profiles/' + data.owner.name"
               v-text="data.owner.name">

            </a> said <span v-text="ago"></span>
          </h5>

          <favorite v-if="signedIn" :reply="data"></favorite>

        </div>
      </div>

      <div class="card-body">
        <div v-if="editing">
          <textarea class="form-control" v-model="body"></textarea>

          <button class="btn btn-xs btn-primary" @click="update">Update</button>
          <button class="btn btn-xs btn-link" @click="editing = false">Cancel</button>

        </div>
        <div v-else v-text="body">
        </div>
      </div>

      <div class="card-footer d-flex" v-if="canUpdate">
        <button class="btn btn-xs btn-secondary" @click="editing = true">Edit</button>
        <button class="btn btn-danger btn-xs" @click="destroy">Delete</button>
      </div>
    </div>
</template>

<script>

    import Favorite from './Favorite.vue';
    import moment from 'moment';

    export default {
      props: [ 'data'],

      components: { Favorite },

      data() {
        return {
          editing: false,
          id: this.data.id,
          body: this.data.body
        }
      },

      computed: {
        ago() {
          return moment(this.data.created_at).fromNow() + '...';
        },
        signedIn() {
          return window.App.signedIn;
        },

        canUpdate() {
          return this.authorize(user => {
            return this.data.user_id == user.id
          });
        },

      },

      methods: {

        update() {
          axios.patch('/replies/' + this.data.id, {
            body: this.body
          }).catch( error =>{
            flash(error.response.data, 'danger');
          });

          this.editing = false;

          flash('Updated!');


        },

        destroy() {
          axios.delete('/replies/' + this.data.id).then(res => {
            this.$emit('deleted', this.data.id); // apparentemente non serve a nulla passare il this.data.id in quanto poi chi riceve l'evento non se ne fa nulla dell'id
          });
        }
      }
    }
</script>