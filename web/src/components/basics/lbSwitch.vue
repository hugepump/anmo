<template>
    <el-switch
    v-model="val"
    :disabled='isDisabled'
    :width='coreWidth'
    :active-icon-class='activeIconClass'
    :inactive-icon-class='inactiveIconClass'
    :active-text='activeText'
    :inactive-text='inactiveText'
    :active-value='activeValue'
    :inactive-value='inactiveValue'
    :active-color='activeColor'
    :inactive-color='inactiveColor'
    :name='name'
    @change="handleSwitchValue"
    >
    </el-switch>
</template>

<script>
export default {
  props:
    {
      opType: {
        type: String,
        default: ''
      },
      value: {
        type: [Boolean, String, Number],
        default: false
      },
      disabled: {
        type: Boolean,
        default: false
      },
      width: {
        type: Number,
        default: 40
      },
      activeIconClass: {
        type: String,
        default: ''
      },
      inactiveIconClass: {
        type: String,
        default: ''
      },
      activeText: String,
      inactiveText: String,
      activeColor: {
        type: String,
        default: ''
      },
      inactiveColor: {
        type: String,
        default: ''
      },
      activeValue: {
        type: [Boolean, String, Number],
        default: true
      },
      inactiveValue: {
        type: [Boolean, String, Number],
        default: false
      },
      name: {
        type: String,
        default: ''
      },
      validateEvent: {
        type: Boolean,
        default: true
      },
      id: String
    },
  data () {
    return {
      val: this.value,
      coreWidth: this.width,
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
  computed: {
    checked () {
      return this.value === this.activeValue
    }
  },
  methods: {
    handleSwitchValue (val) {
      this.$emit('change', val)
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
