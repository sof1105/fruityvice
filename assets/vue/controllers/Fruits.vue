<template>
  <b-container fluid>
    <div class="page-link">
        <router-link :to="'/favs'"><el-button type="success">Favorite page</el-button></router-link>
      </div>
    <el-row :gutter="20" class="filters">
      <el-col  :span="6">
        <el-input v-model="nameSearch" placeholder="Filter by name" />
      </el-col>
      <el-col  :span="6"><el-input v-model="familySearch" placeholder="Filter by family" /></el-col>
      <el-col  :span="6">
        <el-select v-model="nameSelectFilter" multiple placeholder="Select names" style="width: 240px">
          <el-option v-for="item in nameFilterOptions" :key="item" :label="item" :value="item" />
        </el-select>
      </el-col>
      <el-col  :span="6">
        <el-select v-model="familySelectFilter" multiple placeholder="Select families" style="width: 240px">
          <el-option v-for="item in familyFilterOptions" :key="item" :label="item" :value="item" />
        </el-select>
      </el-col>
      
    </el-row>
    <el-table :data="fruits" style="width: 100%" v-if="tableMode">
      <el-table-column prop="name" label="Name" width="180" />
      <el-table-column prop="family" label="Family" width="180" />
      <el-table-column prop="genus" label="Genus" width="180" />
      <el-table-column prop="origorder" label="Order" width="180" />
      <el-table-column label="Nutritions">
        <el-table-column prop="carbohydrates" label="Carbohydrates" width="180" />
        <el-table-column prop="protein" label="Protein" width="180" />
        <el-table-column prop="fat" label="fat" width="180" />
        <el-table-column prop="calories" label="Calories" width="180" />
        <el-table-column prop="sugar" label="Sugar" width="180" />
      </el-table-column>
      <el-table-column label="Favorite" width="180">
        <template #default="scope">
          <el-button type="primary" :icon="scope.row.fav ? StarFilled : Star" circle @click="handleFav(scope.row)"
            :disabled="scope.row.lock" />
        </template>
      </el-table-column>
    </el-table>
    <div class="fruit-box" v-if="!tableMode">
      <el-card class="box-card" v-for="fruit in fruits">
        <template #header>
          <div class="card-header">
            <span class="name">{{ fruit.name }}</span>
          </div>
        </template>
        <el-row>
          <div>family: </div><span>{{ fruit.family }}</span>
        </el-row>
        <el-row>
          <div>genus:</div><span>{{ fruit.genus }}</span>
        </el-row>
        <el-row>
          Nutritions:
        </el-row>
        <el-row>
          <div>carbohydrates:</div><span>{{ fruit.carbohydrates }}</span>
        </el-row>
        <el-row>
          <div>protein:</div><span>{{ fruit.protein }}</span>
        </el-row>
        <el-row>
          <div>fat:</div><span>{{ fruit.fat }}</span>
        </el-row>
        <el-row>
          <div>calories:</div><span>{{ fruit.calories }}</span>
        </el-row>
        <el-row>
          <div>sugar:</div><span>{{ fruit.sugar }}</span>
        </el-row>
        <el-row>
          <div class="fav"><el-button type="primary" :icon="fruit.fav ? StarFilled : Star" circle @click="handleFav(fruit)"
              :disabled="fruit.lock" /></div>
        </el-row>
      </el-card>
    </div>
    <el-pagination layout="prev, pager, next" :total="total" :page-size="itemPerPage"
      @current-change="handlePageChange" />
  </b-container>
</template>

<script>
import axios from "axios";
import { Star, StarFilled } from "@element-plus/icons-vue";
import { debounce } from "../helpers.js";
import { ElNotification } from 'element-plus'
import { h } from 'vue'

export default {
  name: "Fruits",
  data() {
    return {
      fruits: null,
      loading: false,
      page: 1,
      Star: Star,
      StarFilled: StarFilled,
      nameSearch: "",
      familySearch: "",
      nameFilterOptions: [],
      familyFilterOptions: [],
      nameSelectFilter: [],
      familySelectFilter: [],
      fruitApi: "/api/fruits/",
      filtersApi: "/api/filters",
      total: 0,
      itemPerPage: 10,
      tableMode: true
    };
  },
  mounted() {
    this.getFruits();
    this.getFilters();
    this.debounceGetFruits = debounce(this.getFruits, 1000);
  },
  methods: {
    async getFruits() {
      try {
        this.loading = true;
        const params = {};
        if (this.nameSelectFilter && this.nameSelectFilter.length > 0) {
          params.nfilter = [...this.nameSelectFilter]
        }
        if (this.familySelectFilter && this.familySelectFilter.length > 0) {
          params.ffilter = [...this.familySelectFilter]
        }
        if (this.nameSearch) {
          params.name = this.nameSearch
        }
        if (this.familySearch) {
          params.family = this.familySearch;
        }
        const { status, data } = await axios(this.fruitApi + this.page, { params });
        if (status !== 200) {
          throw "Error fetching data";
        }
        const { fruits, page, itemsPerPage, total } = data;
        this.fruits = fruits;
        this.total = total;
        this.itemPerPage = itemsPerPage;
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    async getFilters() {
      try {
        const { status, data } = await axios(this.filtersApi);
        if (status !== 200) {
          throw "Error fetching data";
        }
        const { names, families } = data;
        this.nameFilterOptions = names;
        this.familyFilterOptions = families;

      } catch (e) {
        console.error(e);
      }
    },
    handlePageChange(page) {
      this.page = page
    },
    async handleFav(fruit) {
      if (!fruit.fav) {
        try {
          fruit.lockfav = true
          const { status, data } = await axios.post('/api/fav/' + fruit.id)
          if (status == 200) {
            ElNotification({
              title: 'Added',
              message: h('i', { style: 'color: green' }, fruit.name + ' added to favorites.'),
            })
            fruit.fav = true
          } else
            throw 'Error';
        } catch (error) {
          const msg = error.response?.data?.message;
          console.error(error)
          ElNotification({
            title: 'Error!',
            message: h('i', { style: 'color: red' }, msg || 'An issue occurred.'),
          })
        } finally {
          fruit.lock = false
        }
      } else {
        try {
          fruit.lockfav = true
          const { status, data } = await axios.delete('/api/deletefav/' + fruit.id)
          if (status == 200) {
            ElNotification({
              title: 'Removed',
              message: h('i', { style: 'color: teal' }, fruit.name + ' removed from favorites.'),
            })
            fruit.fav = false
          } else
            throw 'Error'
        } catch (error) {
          console.error(error)
          ElNotification({
            title: 'Error!',
            message: h('i', { style: 'color: red' }, 'An issue occurred.'),
          })
        } finally {
          fruit.lock = false
        }
      }
    }
  },
  watch: {
    nameSearch() {
      this.debounceGetFruits();
    },
    familySearch() {
      this.debounceGetFruits();
    },
    nameSelectFilter() {
      this.debounceGetFruits();
    },
    familySelectFilter() {
      this.debounceGetFruits()
    },
    page() {
      this.getFruits()
    }
  },
};
</script>
