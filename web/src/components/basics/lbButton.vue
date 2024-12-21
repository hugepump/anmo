<template>
  <el-button
    :disabled="isDisabled"
    :type="type"
    :plain="plain"
    :round="round"
    :icon="icon"
    :size="size"
    @click="handleClick"
  >
    <slot></slot>
  </el-button>
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    },
    plain: {
      type: Boolean,
      default: false
    },
    round: {
      type: Boolean,
      default: false
    },
    icon: {
      type: String,
      default: ''
    },
    size: {
      type: String,
      default: 'medium'
    },
    opType: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      isDisabled: this.disabled,
      currentIndex: this.$store.state.operate.currentIndex
    }
  },
  created () {
    let {isOnly, auth, pagePermission} = this.$route.meta
    if (this.opType) {
      if (isOnly) {
        this.isDisabled = auth.indexOf(this.opType) === -1
      } else {
        this.isDisabled = pagePermission[this.currentIndex].auth.indexOf(this.opType) === -1
      }
    }
  },
  methods: {
    handleClick () {
      this.$emit('click')
    }
  }
}
</script>

<style lang="scss" scoped></style>
