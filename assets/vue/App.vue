<template>
  <div class="common-layout">
    <el-container>
      <el-header><h1 class="site-title">fruityvice</h1></el-header>
      <el-main> <router-view /> </el-main>
    </el-container>
  </div>
</template>

<script>
export default {
  name: "App",
  created() {},
};
</script>
