桶排序
---
思想：初始化一个和待排序数组大小一样的桶数组，全部填充0。循环待排序数组，桶数组索引减1等于当前元素值，该索引对应的值+1。
```

```

select sort
---
思想：定义low为最小值索引，遍历数组，比较当前元素和最小值，如果当前元素更小，则把当前元素的索引赋值给low。遍历结束，把low对应的值和数组开头未排序的最小索引指向的元素互换.
```
function selectSort($arr)
{
	$len = count($arr);
	for($i=0;$i<$len-1;$i++) {
		$low = $i;
		for($j=$i+1;$j<$len;$j++) {
			if($arr[$j] < $arr[$low]) {
				$low = $j;
			}
		}
		$temp = $arr[$i];
		$arr[$i] = $arr[$low];
		$arr[$low] = $temp;
	}
	return $arr;
}
var_export(selectSort([1,4,3,2]));
```

insert sort
---
思想：每次把当前元素和前面已排序好的元素比较，若小于，则将大的元素向后移动，直到出现大于，则到达最终位置。当前指针再往后移动一位，再重复以上步骤。
```
function test($arr) {
	$len = count($arr);
	for($i = 1; $i< $len; $i++) {
		$j = $i-1;
		$key = $arr[$i];
		while($j >=0 && $key < $arr[$j]) {
			$arr[$j+1] = $arr[$j];
			$j--;
		}
		$arr[$j+1] = $key;
	}

	return $arr;
}
var_dump(test([1,3,5,4,5,2,0,3,7,8,2,6]));

```

bubble sort
---
思想：a1和a2比较，若a1>a2, 则a1=a2,a2=a1;然后a2和a3比较...,直到a(n-1)和an比较，得到an存放最大值。第二轮重复以上，得到a(n-1)为倒数第二大...
```
$arr= [6,1,3,2,7,4,5];

	$len = count($arr);
	for ($i=0;$i<$len-1;$i++) {
		for ($j=0;$j<$len-1-$i;$j++) {
			if($arr[$j] >$arr[$j+1]) {
				$temp = $arr[$j+1];
				$arr[$j+1]=$arr[$j];
				$arr[$j]=$temp;
			}
		}
	}
var_dump($arr);
```

quick sort
---
思想：分支法，把问题分隔成多个具有相同结构的子问题，递归解决子问题后获得问题的解。
步骤：    
1.设定一个基准    
2.分区，与基准比较，把小于基准的放到左边，大于基准的放到右边，最后将基准交换到最小索引位置，即为基准的最终位置。
3.递归左右分区。
```
function quickSort(&$arr, $left, $right)
{
	if($left < $right) {
    $x = $arr[$left];
    $i = $left;
    $j = $right;
    while($i!=$j) {
    	while($arr[$j] >=$x && $j>$i) {
		$j--;
	}
	while($arr[$i] <= $x && $j>$i){
		$i++;
	}
	if($i<$j) {
		$temp = $arr[$j];
		$arr[$j] = $arr[$i];
		$arr[$i] =$temp;
	}
    }
    $arr[$left] = $arr[$i];
    $arr[$i] = $x;
    quickSort($arr, $left, $i-1);
    quickSort($arr, $i+1, $right);
	}
}
$arr = [6,1,3,4,7,5,8,2,9,8,6];
quickSort($arr, 0, 10);
var_dump($arr);
```




























