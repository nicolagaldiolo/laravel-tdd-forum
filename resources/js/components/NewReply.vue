<template>
  <div class="mt-4">
    <div v-if="signedIn">
      <div class="form-group">
                <textarea name="body"
                          id="body"
                          class="form-control"
                          placeholder="Have something to say?"
                          rows="5"
                          required
                          v-model="body"></textarea>
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
      body: ''
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
            this.body = '';
            flash('Your reply has been posted.');

            this.$emit('created', data);
          });
    }
  }
}
</script>