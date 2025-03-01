<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from 'vue'
import { useStore } from '@/store'

export default defineComponent({
  name: 'AdminUserRowPage',
  props: {
    user: { type: Object, required: true }
  },
  setup (props) {
    const store = useStore()

    const isUpdating = ref(false)
    const profile = ref('user')
    const tmpUser = reactive({
      id: null,
      email: '',
      roles: ['ROLE_ANONYMOUS'],
      username: ''
    })

    function getRoles (): string[] {
      if (profile.value === 'user') {
        return ['ROLE_USER']
      } else {
        return ['ROLE_USER', 'ROLE_ADMIN']
      }
    }
    function initProfile () {
      if (props.user.roles.length === 1) {
        profile.value = 'user'
      } else {
        profile.value = 'admin'
      }
    }
    function initTmpUser () {
      tmpUser.id = props.user.id
      tmpUser.email = props.user.email
      tmpUser.roles = props.user.roles
      tmpUser.username = props.user.username
      initProfile()
    }
    function closeForm () {
      isUpdating.value = false
    }
    function deleteUser () {
      if (confirm('Do you confirm you want to delete this user ?')) {
        store.dispatch('users/deleteUser', props.user.id)
      }
    }
    async function disableUser () {
      try {
        await store.dispatch('users/disableUser', props.user.id)
      } catch (err) {
        alert(err)
      }
    }
    function enableUser () {
      store.dispatch('users/enableUser', props.user.id)
    }
    function initForm () {
      initTmpUser()
      isUpdating.value = true
    }
    async function updateUser () {
      tmpUser.roles = getRoles()
      try {
        await store.dispatch('users/updateUser', tmpUser)
        closeForm()
      } catch (err) {
        alert(err)
      }
    }

    onMounted(() => {
      initTmpUser()
    })

    return { isUpdating, updateUser, tmpUser, closeForm, enableUser, disableUser, deleteUser, initForm, profile }
  }
})
</script>

<template>
  <tr v-if="isUpdating">
    <td colspan="4">
      <form @submit.prevent="updateUser">
        <label for="updateUsername"></label><input required="required" type="text" placeholder="Username" v-model="tmpUser.username" id="updateUsername" />
        <label for="updateEmail"></label><input required="required" type="email" placeholder="Email" v-model="tmpUser.email" id="updateEmail" />
        <label for="updateRoles"></label><select id="updateRoles" v-model="profile"><option value="user">User</option><option value="admin">Admin</option></select>
        <input type="submit" value="Update user" />
        <input type="submit" value="Cancel" @click.prevent="closeForm" />
      </form>
    </td>
  </tr>
  <tr v-else>
    <td>{{ user.username }}</td>
    <td>{{ user.email }}</td>
    <td>{{ user.roles.join(', ') }}</td>
    <td>
      <a
        v-if="!user.isEnabled"
        href="JavaScript:void(0)"
        @click.prevent="enableUser(user)"
        title="Enable"
      >
        <font-aw icon="user-times" />
      </a>
      <a
        v-if="user.isEnabled"
        href="JavaScript:void(0)"
        @click.prevent="disableUser(user)"
        title="Disable"
      >
        <font-aw icon="user-check" />
      </a>
      <a
        href="JavaScript:void(0)"
        @click.prevent="initForm"
      >
        <font-aw :icon="['far', 'edit']" />
      </a>
      <a href="JavaScript:void(0)"
         @click.prevent="deleteUser(user)"
         v-if="!user.isEnabled"
         title="Delete"
      >
        <font-aw :icon="['far', 'trash-alt']" />
      </a>
    </td>
  </tr>
</template>
