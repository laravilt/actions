<script setup lang="ts">
import { DialogRoot, type DialogRootEmits, type DialogRootProps, useForwardPropsEmits } from 'reka-ui'

const props = defineProps</* @vue-ignore */ DialogRootProps>()
const emits = defineEmits</* @vue-ignore */ DialogRootEmits>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <DialogRoot
    data-slot="dialog"
    v-bind="forwarded"
  >
    <slot />
  </DialogRoot>
</template>
