<template>

  <li class="nav-item dropdown" v-if="notifications.length">

    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
      <i class="fa fa-bell" aria-hidden="true"></i>
    </a>

    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
      <a v-for="notification in notifications" class="dropdown-item" :href="notification.data.link" @click="markAsRead(notification)">
        {{ notification.data.message }}
      </a>
    </div>
  </li>

</template>

<script>

  export default {
    data() {
      return {
        notifications: false,
      }
    },

    created() {
      this.fetchData()
    },

    methods: {

      fetchData(){
        axios.get('/profiles/' + window.App.user.name + '/notification').then(({data}) => {
          this.notifications = data
        })
      },

      markAsRead(notification){
        axios.delete('/profiles/' + window.App.user.name + '/notification/' + notification.id);
      },

    }
  }

</script>