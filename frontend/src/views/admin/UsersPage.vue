<script lang="ts">
import { HTMLElementEvent } from '@/utils/types'
import AdminUserRow from '@/views/admin/UserRow.vue'
import { onMounted, computed, defineComponent, reactive } from 'vue'
import { useStore } from '@/store'

export default defineComponent({
  name: 'AdminUserPage',
  components: {
    AdminUserRow
  },
  setup () {
    const store = useStore()

    const newUser = reactive({
      email: '',
      isEnabled: true,
      roles: ['ROLE_USER'],
      username: ''
    })

    const userRequests = computed(() => store.state.users.actionRequest)
    const users = computed(() => store.state.users.users)

    function addUser () {
      store.dispatch('users/addUser', newUser)
    }
    function getAllUsers () {
      store.dispatch('users/getAll')
    }
    function setRoles (event: HTMLElementEvent<HTMLSelectElement>) {
      if (event.target.value === 'admin') {
        newUser.roles.push('ROLE_ADMIN')
      } else {
        const i = newUser.roles.indexOf('ROLE_ADMIN')
        if (i > -1) {
          newUser.roles.splice(i, 1)
        }
      }
    }

    onMounted(() => {
      getAllUsers()
    })

    return { userRequests, users, addUser, newUser, setRoles }
  }
})
</script>

<template>
  <div class="container">
    <h1>Users</h1>
    <div class="table-responsive">
      <!-- pourquoi table-striped et table-hover marchent po ? -->
      <table class="table">
        <tr>
          <th>Username</th>
          <th>Email</th>
          <th>roles</th>
          <th>Action</th>
        </tr>
        <admin-user-row
          v-for="user in users"
          :key="user.id"
          :user="user"
        />
        <tr>
          <td colspan="3">
            <form @submit.prevent="addUser">
              <label for="newUsername"></label><input required="required" type="text" placeholder="Username" v-model="newUser.username" id="newUsername" />
              <label for="newEmail"></label><input required="required" type="email" placeholder="Email" v-model="newUser.email" id="newEmail" />
              <label for="newRoles"></label><select id="newRoles" @change="setRoles"><option value="user">User</option><option value="admin">Admin</option></select>
              <input type="submit" value="New user" />
            </form>
            <span class="err">{{ userRequests.addUser.message }}</span>
        </td>
        </tr>
      </table>
    </div>
  </div>
</template>
