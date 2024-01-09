<?php


class ObjectTranspose
{

    public function __construct()
    {
        $this->branch = [];
    }

    public function keyOperationOnObject(
        $object,
        string $layerIndex,
        $function
    )
    {
        $result = $this->unNest($object);
        $alphabet = array_flip(self::alphabetRange());
        foreach ($result as $k => &$v) {
            $v[$alphabet[$layerIndex]] = $function($v[$alphabet[$layerIndex]]);
        }
        return $this->nestArrayOfObjects($result);
    }


    public static function alphabetRange()
    {
        return range("A", "Z");
    }


    public function removeLeafNodes(
        $object,
        array|string $columnNames
    )
    {
        if (is_string($columnNames)) $columnNames = [$columnNames];
        $result = $this->unNest($object);
        $leafIndex = sizeof($result[0]) - 1;
        foreach ($result as $k => $v) {
            if (in_array($v[$leafIndex - 1], $columnNames)) {
                unset($result[$k]);
            }
        }
        return $this->nestArrayOfObjects($result);
    }


    public function reduceBallastLeaf(
        $object
    )
    {
        $result = $this->unNest($object);
        $leafIndex = sizeof($result[0]) - 1;
        foreach ($result as $k => &$v) {
            unset($v[$leafIndex - 1]);
            $v = array_values($v);
        }
        return $this->nestArrayOfObjects($result);
    }


    public function transpose(
        $object,
        string $order
    )
    {
        $order = str_split($order);

        $nodeBranchLength = null;
        $this->branch = [];
        $pointer = &$object;
        $result = [];
        while (!empty($object)) {
            $this->unNestObject($object, $pointer);
            if (!empty($this->branch)) {
                # A bit of a shitty hack to make the responses of unNestObject work
                if ($nodeBranchLength === NULL) {
                    $nodeBranchLength = sizeof($this->branch);
                }
                if (sizeof($this->branch) === $nodeBranchLength) {
                    $this->branch = array_merge(array_flip($order), array_combine(array_slice(range("A", "Z"), 0, sizeof($this->branch)), $this->branch));
                    $result = $this->nestObject($this->branch, $result);
                }
            }
            $this->branch = [];
        }
        return $result;
    }

    public function unNest(
        $object
    )
    {
        $branches = [];
        $nodeBranchLength = null;
        $this->branch = [];
        $pointer = &$object;

        while (!empty($object)) {
            $this->unNestObject($object, $pointer);
            if (!empty($this->branch)) {
                if ($nodeBranchLength === NULL) {
                    $nodeBranchLength = sizeof($this->branch);
                }
                if (sizeof($this->branch) === $nodeBranchLength) {
                    $branches[] = $this->branch;
                }
            }
            $this->branch = [];
        }
        return $branches;
    }

    private function unNestObject(
        $object,
        &$pointer
    )
    {
        if (!is_array($object)) {
            $this->branch[] = $object;
            return true;
        }
        foreach ($object as $key => $value) {
            if (empty($value)) {
                unset($pointer[$key]);
                return false;
            }
            $this->branch[] = $key;
            if (is_array($value)) $pointer = &$pointer[$key];
            if ($this->unNestObject($object[$key], $pointer)) {
                unset($pointer[$key]);
            }
            return false;
        }
        return false;
    }

    public function nestArrayOfObjects(
        $array
    )
    {
        $accumulation = [];
        foreach ($array as $index => $value) {
            $accumulation = $this->nestObject($value, $accumulation);
        }
        return $accumulation;
    }

    public function nestObject($array, $accumulation = [])
    {
        $pointer = &$accumulation;
        $arraySize = sizeof($array);
        foreach (array_values($array) as $index => $value) {
            if ($index == $arraySize - 1) {
                $pointer = $value;
                break;
            }
            $pointer = &$pointer[$value];
        }
        return $accumulation;
    }


    public function leafArithmeticOperation(
        $object,
        $operandA,
        $operandB,
        $newColumnName,
        $operation
    )
    {
        $result = $this->unNest($object);
        $leafIndex = sizeof($result[0]) - 1;
        foreach ($result as $k => &$v) {
            $tmp = $v;
            unset($tmp[$leafIndex - 1]);
            unset($tmp[$leafIndex]);
            $v["_id"] = md5(implode($v));
            $v["hash"] = md5(implode($tmp));
        }
        unset($v);

        $hold = [];
        foreach ($result as $k => $v) {
            $hold[$v["hash"]][$v[$leafIndex - 1]] = $v[$leafIndex];
        }

        foreach ($hold as $hash => $hashInfo) {
            if (!is_string($operandB)) {
                $diff = $operation($hashInfo[$operandA], $operandB);
            } else {
                $diff = $operation($hashInfo[$operandA], $hashInfo[$operandB]);
            }

            foreach ($result as $k => $v) {
                if ($v["hash"] == $hash) {
                    $row = $v;
                    $row[$leafIndex - 1] = $newColumnName;
                    $row[$leafIndex] = $diff;
                    $result[] = $row;
                    break;
                }
            }
        }

        foreach ($result as $k => $v) {
            unset($result[$k]["hash"]);
            unset($result[$k]["_id"]);
        }
        return $this->nestArrayOfObjects($result);
    }


}


