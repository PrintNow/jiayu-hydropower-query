// 电费容器 DOM
const $electric = document.getElementById("electric-wrap")

// 水费容器 DOM
const $water = document.getElementById("water-wrap")

let electricChart = echarts.init($electric)
const electricOption = option = {
  title: {
    text: '用电量明细图',
    subtext: '图表数据仅供参考，请以事实为准'
  },
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'cross'
    }
  },
  toolbox: {
    show: true,
    feature: {
      saveAsImage: {}
    }
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '3%',
    containLabel: true
  },
  xAxis: {
    type: 'category',
    // boundaryGap: false,
    data: [],
    axisTick: {
      alignWithLabel: true
    },
    axisLabel: {
      formatter(value, index) {
        if (value === dayjs().format("YYYY-MM-DD")) return "今天"


        const dayjsObject = dayjs(value, "YYYY-MM-DD")
        if (dayjsObject.$y === dayjs().$y) {
          value = value.replace(/-/g, '/')
          return value.replace(`${dayjsObject.$y}/`, '')
        }

        return value
      }
    }
  },
  yAxis: {
    type: 'value',
    axisLabel: {
      formatter: '{value} kW·h'
    },
    axisPointer: {
      snap: true
    }
  },
  series: [
    {
      name: '用电趋势',
      type: 'line',
      smooth: true,
      data: [],
    },
    {
      name: '日用电量',
      type: 'bar',
      smooth: true,
      barWidth: '60%',
      data: [],
      label: {
        show: true,
        position: 'top',
        formatter(value, index) {
          return `${value.data}`;
        }
      }
    }
  ]
}

const $formDom = $(".room-list-wrap form")
getRoomList()

/**
 * 获取房间列表
 */
function getRoomList() {
  $.ajax({
    method: 'GET',
    url: 'api.php',
    data: {
      type: 'get-room-list'
    },
    dataType: 'json',
    success: function (data) {
      // 如果没有登录就走这里
      if (data.code === 403 || data.code === 401) return showLoginWrap()

      if (data.code !== 0) return mdui.snackbar({
        message: data.msg || '未知的错误，请刷新界面'
      });

      $formDom.html('')
      for (let item of data.data.records) {
        $formDom.append(`<label class="mdui-radio">
                <input type="radio" name="room-list" value="${item.roomId}"/>
                <i class="mdui-radio-icon"></i>
                <div class="room-item mdui-typo">
                  <div class="room-position">
                    ${item.communityName}
                  </div>
                  <div class="room-id">
                    房间号：<a>${item.roomNum}</a>
                  </div>
                </div>
              </label>`)
      }

      addRadioCheckedListen()

      let checkedRoom = getConfig('checkedRoom', false)
      if (checkedRoom !== false) {
        console.log('选择历史的')
        $(`input[value="${checkedRoom}"]`).first().trigger('click');
      } else {
        console.log('选择默认的')
        $(`input[type="radio"]`).first().trigger('click');
      }
      getElectric()
    }
  });
}

/**
 * 监听 Radio 选择事件
 */
function addRadioCheckedListen() {
  mdui.mutation()
  $(document).on('change checked', 'input[type=radio]', function (e) {
    let roomId = e.target.value
    setConfig('checkedRoom', roomId + '')
    getElectric()
  });
}

/**
 * 获取水电费数据
 */
function getElectric() {
  $.ajax({
    method: 'GET',
    url: 'api.php',
    data: {
      type: 'get-electric-bill',
      roomId: getConfig('checkedRoom')
    },
    dataType: 'json',
    success: function (data) {
      // 如果没有登录就走这里
      if (data.code === 403 || data.code === 401) return showLoginWrap()

      if (data.code !== 0) return mdui.snackbar({
        message: data.msg || '未知的错误，请刷新界面'
      });

      setElectricOption(data.data.records)
    }
  })
}

function showLoginWrap() {

}

/**
 * 取得配置
 *
 * @param param
 * @param defaultVal
 * @returns {string}
 */
function getConfig(param = '', defaultVal = '') {
  let config = JSON.parse(localStorage.getItem('jiayu_config')) || {}
  return config[param] || defaultVal
}

/**
 * 设置配置
 *
 * @param key
 * @param val
 */
function setConfig(key = '', val = '') {
  let config = JSON.parse(localStorage.getItem('jiayu_config')) || {}
  config[key] = val
  localStorage.setItem('jiayu_config', JSON.stringify(config))
}

/**
 * 设置电费相关配置信息
 * @param data
 */
function setElectricOption(data) {
  let startVal = data[Object.keys(data).length - 1].waterElectricityData

  let obj = {}
  data.forEach((value => {
    let date = value.newtime.split(" ")[0]
    // 取一天中最新的那一条数据
    if (!obj[date]) obj[date] = (value.waterElectricityData - startVal).toFixed(2)
  }))

  electricOption.xAxis.data = Object.keys(obj).reverse()
  electricOption.series[1].data = Object.values(obj).reverse().map((value, index, array) => {
    if (index === 0) return value
    return (value - array[index - 1]).toFixed(2)
  })
  electricChart.setOption(electricOption)
}