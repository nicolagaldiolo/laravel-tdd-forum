<template>
  <div class="mt-4">
    <div v-if="signedIn">
      <div class="form-group">
        <wysiwyg v-model="body" placeholder="Have something to say?" :shouldClear="completed"></wysiwyg>
      </div>

      <button type="submit"
              class="btn btn-default"
              @click="addReply">Post</button>
    </div>

    <p class="text-center" v-else>
      Please <a href="/login">sign in</a> to participate in this
      discussion.
    </p>
  </div>
</template>

<script>

import Tribute from "tributejs";

export default {
  data() {
    return {
      body: '',
      completed: false
    };
  },

  mounted() {
    var delay;
    var tributeMultipleTriggers = new Tribute({
      // The symbol that starts the lookup
      loadingItemTemplate: '<div style="padding: 16px">Loading</div>',

      // function retrieving an array of objects
      values: function(text, cb) {

        clearTimeout(delay);

        delay = setTimeout(()=>{
          let currentData = [];

          axios.get('/api/users', {
            params: {
              name: text
            }
          }).then(({data}) => {
            data.forEach(entry => currentData.push({ name: entry}))
            cb(currentData);
          }).catch(error => {
            console.log(error.response.data.error);
            cb(currentData);
          });
        }, 300);

      },
      lookup: "name",
      fillAttr: "name",
    });

    tributeMultipleTriggers.attach(document.getElementById("body"));
  },

  methods: {
    addReply() {

      axios.post(location.pathname + '/replies', { body: this.body })
          .catch( error =>{
            flash(error.response.data, 'danger');
          })
          .then(({data}) => {
            this.completed = true;
            flash('Your reply has been posted.');

            this.$emit('created', data);
          });
    }
  }
}
</script>