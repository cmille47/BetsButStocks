#!/usr/bin/env python

import sys
from math import *

def prob(z):
    #'Cumulative distribution function for the standard normal distribution'
    return (1.0 + erf(z / sqrt(2.0))) / 2.0

if __name__ == "__main__":

    z = float(sys.argv[1])
    gt_or_lt = sys.argv[2]

    p = prob(z)

    if gt_or_lt == 'gt':
        p = 1 - p

    print(round(p, 4))